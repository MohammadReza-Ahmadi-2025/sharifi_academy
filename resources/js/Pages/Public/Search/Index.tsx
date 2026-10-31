import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { Link, router } from '@inertiajs/react';
import { BookOpen, GraduationCap, Layers, Newspaper, Search, Tag } from 'lucide-react';
import { FormEvent, useState } from 'react';

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
    kindOptions: { value: string; label: string }[];
};

const KIND_META: Record<string, { label: string; icon: typeof GraduationCap }> = {
    course: { label: 'دوره‌ها', icon: GraduationCap },
    book: { label: 'کتاب‌ها', icon: BookOpen },
    post: { label: 'وبلاگ', icon: Newspaper },
    category: { label: 'دسته‌ها', icon: Layers },
    brand: { label: 'برندها', icon: Tag },
};

export default function SearchIndex({ q, kind, total, groups, kindOptions }: Props) {
    const [query, setQuery] = useState(q);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        router.get(
            '/search',
            { q: query, ...(kind ? { kind } : {}) },
            { preserveState: true, replace: true },
        );
    };

    const groupEntries = Object.entries(groups);

    return (
        <AppLayout>
            <SiteDefaultHead title={q ? `جستجو: ${q}` : 'جستجو'} />

            <div className="mx-auto max-w-4xl px-4 py-10 md:py-14">
                <h1 className="text-2xl font-bold text-slate-900">جستجو در آکادمی</h1>
                <p className="mt-1 text-sm text-slate-600">دوره، کتاب، وبلاگ، برند و دسته</p>

                <form onSubmit={submit} className="mt-6 flex flex-wrap gap-2">
                    <div className="relative min-w-[240px] flex-1">
                        <Search className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400" />
                        <input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="عبارت جستجو…"
                            className="w-full rounded-2xl border border-slate-200 bg-white py-3 pr-10 pl-4 text-sm outline-none ring-primary/30 focus:ring-2"
                        />
                    </div>
                    <button
                        type="submit"
                        className="rounded-2xl bg-primary px-5 py-3 text-sm font-semibold text-on-primary"
                    >
                        جستجو
                    </button>
                </form>

                <div className="mt-4 flex flex-wrap gap-2">
                    {kindOptions.map((option) => {
                        const active = (kind ?? '') === option.value;
                        return (
                            <Link
                                key={option.value || 'all'}
                                href={`/search?q=${encodeURIComponent(q)}${option.value ? `&kind=${option.value}` : ''}`}
                                className={`rounded-full px-3 py-1.5 text-xs font-semibold ${
                                    active
                                        ? 'bg-primary text-on-primary'
                                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                                }`}
                            >
                                {option.label}
                            </Link>
                        );
                    })}
                </div>

                <p className="mt-6 text-sm text-slate-500">
                    {q.length < 2
                        ? 'حداقل ۲ حرف وارد کنید.'
                        : total > 0
                          ? `${total.toLocaleString('fa-IR')} نتیجه برای «${q}»`
                          : `نتیجه‌ای برای «${q}» پیدا نشد.`}
                </p>

                <div className="mt-6 space-y-8">
                    {groupEntries.map(([groupKind, items]) => {
                        const meta = KIND_META[groupKind] ?? { label: groupKind, icon: Search };
                        const Icon = meta.icon;

                        return (
                            <section key={groupKind}>
                                <h2 className="mb-3 flex items-center gap-2 text-base font-bold text-slate-900">
                                    <Icon className="size-4 text-primary" aria-hidden />
                                    {meta.label}
                                </h2>
                                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                    {items.map((item) => (
                                        <li key={item.id}>
                                            <Link
                                                href={item.url}
                                                className="block px-4 py-3 transition hover:bg-slate-50"
                                            >
                                                <p className="font-semibold text-slate-900">{item.title}</p>
                                                {item.subtitle ? (
                                                    <p className="mt-0.5 text-xs text-slate-500">{item.subtitle}</p>
                                                ) : null}
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
