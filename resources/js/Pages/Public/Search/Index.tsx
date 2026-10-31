import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { BookOpen, GraduationCap, Layers, Newspaper, Search, Tag } from 'lucide-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';

type Item = {
    id: string;
    kind: string;
    title: string;
    subtitle: string;
    url: string;
};

type Props = {
    q: string;
    kind: string | null;
    total: number;
    groups: Record<string, Item[]>;
    canonical: string;
    kindOptions: { value: string; label: string }[];
};

const KIND_META: Record<string, { label: string; icon: typeof GraduationCap; chip: string }> = {
    course: {
        label: 'دوره‌ها',
        icon: GraduationCap,
        chip: 'bg-primary/10 text-primary',
    },
    book: {
        label: 'محصولات',
        icon: BookOpen,
        chip: 'bg-amber-500/15 text-amber-900',
    },
    post: {
        label: 'وبلاگ',
        icon: Newspaper,
        chip: 'bg-sky-500/10 text-sky-800',
    },
    category: {
        label: 'دسته‌ها',
        icon: Layers,
        chip: 'bg-slate-500/10 text-slate-700',
    },
    brand: {
        label: 'برندها',
        icon: Tag,
        chip: 'bg-violet-500/10 text-violet-800',
    },
};

const GROUP_ORDER = ['course', 'book', 'post', 'category', 'brand'];

export default function SearchIndex({ q, kind, total, groups, canonical, kindOptions }: Props) {
    const { app } = usePage<PageProps>().props;
    const [query, setQuery] = useState(q);
    const pageTitle = q.trim() ? `جستجو: ${q.trim()}` : 'جستجو';
    const pageDescription = q.trim()
        ? `نتایج جستجو برای «${q.trim()}» در ${app.name} — دوره، محصول، وبلاگ، برند و دسته.`
        : `جستجو در ${app.name} بین دوره‌ها، محصولات، مقالات، برندها و دسته‌ها.`;

    useEffect(() => {
        setQuery(q);
    }, [q]);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        const next = query.trim();
        router.get(
            '/search',
            { q: next, ...(kind ? { kind } : {}) },
            { preserveScroll: true, replace: true },
        );
    };

    const groupEntries = useMemo(() => {
        const entries = Object.entries(groups);
        entries.sort(([a], [b]) => {
            const ai = GROUP_ORDER.indexOf(a);
            const bi = GROUP_ORDER.indexOf(b);

            return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
        });

        return entries;
    }, [groups]);

    return (
        <AppLayout mainClassName="bg-slate-50/70">
            <SiteDefaultHead title={pageTitle} description={pageDescription} />
            <Head>
                {/* Query URLs must not be indexed (thin/duplicate SERP noise). */}
                <meta head-key="robots" name="robots" content="noindex, follow" />
                <link head-key="seo-canonical" rel="canonical" href={canonical} />
                <meta head-key="og:url" property="og:url" content={canonical} />
            </Head>

            <div className="border-b border-slate-200/80 bg-[rgb(12_42_74)] text-white">
                <div className="mx-auto max-w-4xl px-4 py-10 md:py-12">
                    <p className="text-xs font-medium tracking-[0.14em] text-sky-200/80 uppercase">Search</p>
                    <h1 className="mt-1 text-2xl font-bold md:text-3xl">جستجو در آکادمی</h1>
                    <p className="mt-2 text-sm text-sky-100/80">دوره، محصول، وبلاگ، برند و دسته</p>

                    <form onSubmit={submit} className="mt-6 flex flex-col gap-2 sm:flex-row">
                        <div className="relative min-w-0 flex-1">
                            <Search
                                className="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-slate-400"
                                aria-hidden
                            />
                            <input
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="مثلاً کتیا، لاراول، برند…"
                                autoFocus
                                className="w-full rounded-2xl border-0 bg-white py-3.5 pr-11 pl-4 text-sm text-slate-900 shadow-lg outline-none ring-2 ring-transparent focus:ring-sky-300/60"
                            />
                        </div>
                        <button
                            type="submit"
                            className="rounded-2xl bg-sky-400 px-6 py-3.5 text-sm font-bold text-[rgb(12_42_74)] transition hover:bg-sky-300"
                        >
                            جستجو
                        </button>
                    </form>
                </div>
            </div>

            <div className="mx-auto max-w-4xl px-4 py-8 md:py-10">
                <div className="flex flex-wrap gap-2">
                    {kindOptions.map((option) => {
                        const active = (kind ?? '') === option.value;

                        return (
                            <Link
                                key={option.value || 'all'}
                                href={`/search?q=${encodeURIComponent(q)}${option.value ? `&kind=${option.value}` : ''}`}
                                className={cn(
                                    'rounded-full px-3.5 py-1.5 text-xs font-semibold transition',
                                    active
                                        ? 'bg-[rgb(12_42_74)] text-white'
                                        : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100',
                                )}
                                preserveScroll
                            >
                                {option.label}
                            </Link>
                        );
                    })}
                </div>

                <p className="mt-5 text-sm text-slate-500">
                    {q.trim().length < 2
                        ? 'حداقل ۲ حرف وارد کنید تا نتایج نمایش داده شود.'
                        : total > 0
                          ? `${total.toLocaleString('fa-IR')} نتیجه برای «${q}»`
                          : `نتیجه‌ای برای «${q}» پیدا نشد.`}
                </p>

                {q.trim().length >= 2 && total === 0 ? (
                    <div className="mt-8 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center">
                        <Search className="mx-auto size-8 text-slate-300" aria-hidden />
                        <p className="mt-3 text-sm font-medium text-slate-700">چیزی پیدا نشد</p>
                        <p className="mt-1 text-xs text-slate-500">املا را بررسی کنید یا عبارت کوتاه‌تری امتحان کنید.</p>
                    </div>
                ) : null}

                <div className="mt-6 space-y-8">
                    {groupEntries.map(([groupKind, items]) => {
                        const meta = KIND_META[groupKind] ?? {
                            label: groupKind,
                            icon: Search,
                            chip: 'bg-slate-100 text-slate-600',
                        };
                        const Icon = meta.icon;

                        return (
                            <section key={groupKind} aria-labelledby={`search-group-${groupKind}`}>
                                <div className="mb-3 flex items-center justify-between gap-3">
                                    <h2
                                        id={`search-group-${groupKind}`}
                                        className="flex items-center gap-2 text-base font-bold text-slate-900"
                                    >
                                        <span className={cn('flex size-8 items-center justify-center rounded-xl', meta.chip)}>
                                            <Icon className="size-4" aria-hidden />
                                        </span>
                                        {meta.label}
                                    </h2>
                                    <span className="text-xs text-slate-400">
                                        {items.length.toLocaleString('fa-IR')} مورد
                                    </span>
                                </div>

                                <ul className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                                    {items.map((item, index) => (
                                        <li
                                            key={item.id}
                                            className={cn(index > 0 && 'border-t border-slate-100')}
                                        >
                                            <Link
                                                href={item.url}
                                                className="flex items-start gap-3 px-4 py-3.5 transition hover:bg-slate-50"
                                            >
                                                <span className="min-w-0 flex-1">
                                                    <span className="block font-semibold text-slate-900">
                                                        {item.title}
                                                    </span>
                                                    {item.subtitle ? (
                                                        <span className="mt-0.5 block text-xs leading-relaxed text-slate-500">
                                                            {item.subtitle}
                                                        </span>
                                                    ) : null}
                                                </span>
                                                <span
                                                    className={cn(
                                                        'mt-0.5 shrink-0 rounded-md px-2 py-0.5 text-[10px] font-medium',
                                                        meta.chip,
                                                    )}
                                                >
                                                    {meta.label.replace(/‌?ها$/, '')}
                                                </span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </section>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}
