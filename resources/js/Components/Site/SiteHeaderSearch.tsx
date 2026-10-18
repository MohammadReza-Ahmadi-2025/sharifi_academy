import { cn } from '@/lib/utils';
import { Link, router } from '@inertiajs/react';
import { BookOpen, GraduationCap, Layers, Newspaper, Search, Tag, TrendingUp } from 'lucide-react';
import { useCallback, useEffect, useId, useMemo, useRef, useState } from 'react';

type SuggestionKind = 'course' | 'book' | 'category' | 'brand' | 'post';

type SearchSuggestion = {
    id: string;
    kind: SuggestionKind;
    title: string;
    subtitle: string;
    url: string;
};

const KIND_META: Record<
    SuggestionKind,
    { label: string; icon: typeof GraduationCap; iconClass: string }
> = {
    course: { label: 'دوره', icon: GraduationCap, iconClass: 'bg-primary/10 text-primary' },
    book: { label: 'کتاب', icon: BookOpen, iconClass: 'bg-amber-500/15 text-amber-800' },
    category: { label: 'دسته', icon: Layers, iconClass: 'bg-slate-500/10 text-slate-700' },
    brand: { label: 'برند', icon: Tag, iconClass: 'bg-violet-500/10 text-violet-800' },
    post: { label: 'وبلاگ', icon: Newspaper, iconClass: 'bg-sky-500/10 text-sky-800' },
};

type Props = {
    className?: string;
    variant?: 'header' | 'drawer' | 'icon';
    onNavigate?: () => void;
};

function normalizeQuery(value: string): string {
    return value.trim().replace(/\s+/g, ' ');
}

export function SiteHeaderSearch({ className, variant = 'header', onNavigate }: Props) {
    const inputId = useId();
    const rootRef = useRef<HTMLDivElement>(null);
    const abortRef = useRef<AbortController | null>(null);
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [activeIndex, setActiveIndex] = useState(-1);
    const [items, setItems] = useState<SearchSuggestion[]>([]);
    const [loading, setLoading] = useState(false);

    const normalizedQuery = normalizeQuery(query);

    const close = useCallback(() => {
        setOpen(false);
        setActiveIndex(-1);
    }, []);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: MouseEvent) => {
            if (rootRef.current && !rootRef.current.contains(event.target as Node)) {
                close();
            }
        };

        document.addEventListener('mousedown', onPointerDown);

        return () => document.removeEventListener('mousedown', onPointerDown);
    }, [open, close]);

    useEffect(() => {
        if (normalizedQuery.length < 2) {
            setItems([]);
            setLoading(false);
            abortRef.current?.abort();
            abortRef.current = null;

            return;
        }

        const controller = new AbortController();
        abortRef.current?.abort();
        abortRef.current = controller;

        const timer = window.setTimeout(async () => {
            setLoading(true);

            try {
                const response = await fetch(
                    `/search/autocomplete?q=${encodeURIComponent(normalizedQuery)}`,
                    {
                        headers: { Accept: 'application/json' },
                        signal: controller.signal,
                    },
                );

                if (!response.ok) {
                    setItems([]);
                    return;
                }

                const payload = (await response.json()) as { items?: SearchSuggestion[] };
                setItems(Array.isArray(payload.items) ? payload.items : []);
            } catch (error) {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }

                setItems([]);
            } finally {
                if (!controller.signal.aborted) {
                    setLoading(false);
                }
            }
        }, 280);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [normalizedQuery]);

    const displayed = useMemo(() => items, [items]);

    const showPanel = open;
    const listId = `${inputId}-listbox`;

    const handleSelect = () => {
        close();
        setQuery('');
        onNavigate?.();
    };

    const onKeyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (!showPanel || displayed.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((prev) => (prev + 1) % displayed.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((prev) => (prev <= 0 ? displayed.length - 1 : prev - 1));
        } else if (event.key === 'Enter') {
            const target = activeIndex >= 0 ? displayed[activeIndex] : displayed[0];
            if (target) {
                event.preventDefault();
                close();
                setQuery('');
                onNavigate?.();
                router.visit(target.url);
            }
        }
    };

    const panel = showPanel ? (
        <div
            id={listId}
            role="listbox"
            aria-label="پیشنهادهای جستجو"
            className={cn(
                'overflow-hidden rounded-2xl border border-secondary/80 bg-neutral-white text-neutral-black shadow-xl shadow-black/10',
                variant === 'header'
                    ? 'absolute top-[calc(100%+0.5rem)] z-[60] w-[min(100vw-2rem,22rem)] animate-in fade-in slide-in-from-top-1 duration-200 ltr:left-0 rtl:right-0'
                    : variant === 'icon'
                      ? 'mt-2 w-full'
                      : 'mt-3 w-full',
            )}
        >
            <div className="flex items-center gap-2 border-b border-secondary/60 bg-slate-50/90 px-3 py-2.5 text-xs font-medium text-slate-600">
                <TrendingUp className="size-3.5 text-primary" aria-hidden />
                {normalizedQuery.length >= 2
                    ? loading
                      ? 'در حال جستجو…'
                      : `نتایج برای «${query.trim()}»`
                    : 'حداقل ۲ حرف تایپ کنید'}
            </div>

            {normalizedQuery.length >= 2 && !loading && displayed.length > 0 && (
                <ul className="max-h-72 space-y-0.5 overflow-y-auto overscroll-contain p-2">
                    {displayed.map((item, index) => {
                        const meta = KIND_META[item.kind];
                        const Icon = meta.icon;
                        const isActive = activeIndex === index;

                        return (
                            <li key={item.id} role="presentation">
                                <Link
                                    href={item.url}
                                    role="option"
                                    aria-selected={isActive}
                                    onClick={handleSelect}
                                    className={cn(
                                        'flex items-center gap-3 rounded-xl px-2.5 py-2.5 transition',
                                        isActive ? 'bg-primary/8 ring-1 ring-primary/20' : 'hover:bg-slate-50',
                                    )}
                                    onMouseEnter={() => setActiveIndex(index)}
                                >
                                    <span
                                        className={cn(
                                            'flex size-10 shrink-0 items-center justify-center rounded-xl',
                                            meta.iconClass,
                                        )}
                                        aria-hidden
                                    >
                                        <Icon className="size-5" />
                                    </span>
                                    <span className="min-w-0 flex-1 text-start">
                                        <span className="block truncate text-sm font-semibold text-neutral-black">
                                            {item.title}
                                        </span>
                                        <span className="block truncate text-xs text-slate-500">{item.subtitle}</span>
                                    </span>
                                    <span className="shrink-0 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                        {meta.label}
                                    </span>
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            )}

            {normalizedQuery.length >= 2 && !loading && displayed.length === 0 && (
                <p className="px-4 py-6 text-center text-sm text-slate-500">موردی پیدا نشد.</p>
            )}

            {normalizedQuery.length < 2 && (
                <p className="px-4 py-6 text-center text-sm text-slate-500">
                    دوره، کتاب، وبلاگ، برند یا دسته را جستجو کنید.
                </p>
            )}

            <div className="border-t border-secondary/60 px-3 py-2 text-[11px] text-slate-400">
                Enter برای انتخاب · Esc برای بستن
            </div>
        </div>
    ) : null;

    const fieldClass = cn(
        'flex items-center gap-2 rounded-full px-3 py-2 transition-all duration-200',
        variant === 'header' &&
            (open
                ? 'border border-white/40 bg-neutral-white text-neutral-black shadow-md ring-2 ring-white/30'
                : 'border border-white/25 bg-white/10 text-on-primary'),
        (variant === 'drawer' || variant === 'icon') &&
            (open
                ? 'border border-white/35 bg-neutral-white text-neutral-black shadow-md'
                : 'border border-white/20 bg-white/10 text-on-primary'),
        variant === 'icon' && open && 'border-slate-200 bg-neutral-white shadow-lg ring-1 ring-black/5',
    );

    const inputClass = cn(
        'w-full min-w-0 bg-transparent text-sm focus:outline-none',
        open ? 'text-neutral-black placeholder:text-slate-400' : 'text-on-primary placeholder:text-on-primary/55',
    );

    if (variant === 'icon' && !open) {
        return (
            <div ref={rootRef} className={cn('relative', className)}>
                <button
                    type="button"
                    className="rounded-lg p-2 text-on-primary transition hover:bg-primary-hover"
                    aria-label="جستجو"
                    aria-expanded={false}
                    onClick={() => setOpen(true)}
                >
                    <Search className="size-6" aria-hidden />
                </button>
            </div>
        );
    }

    if (variant === 'icon' && open) {
        return (
            <>
                <button
                    type="button"
                    className="fixed inset-0 z-[64] bg-neutral-black/40 lg:hidden"
                    aria-label="بستن جستجو"
                    onClick={close}
                />
                <div ref={rootRef} className={cn('fixed inset-x-3 top-3 z-[65] lg:hidden', className)}>
                    <label className="sr-only" htmlFor={inputId}>جستجو در سایت</label>
                    <div className={fieldClass}>
                        <Search className="size-4 shrink-0 text-primary" aria-hidden />
                        <input
                            id={inputId}
                            type="search"
                            role="combobox"
                            aria-expanded={showPanel}
                            aria-controls={showPanel ? listId : undefined}
                            aria-autocomplete="list"
                            placeholder="جستجو در دوره‌ها و کتاب‌ها…"
                            value={query}
                            autoComplete="off"
                            autoFocus
                            className="w-full min-w-0 bg-transparent text-sm text-neutral-black placeholder:text-slate-400 focus:outline-none"
                            onChange={(event) => {
                                setQuery(event.target.value);
                                setActiveIndex(-1);
                            }}
                            onKeyDown={onKeyDown}
                        />
                    </div>
                    {panel}
                </div>
            </>
        );
    }

    return (
        <div ref={rootRef} className={cn('relative', className)}>
            <label className="sr-only" htmlFor={inputId}>جستجو در سایت</label>
            <div className={fieldClass}>
                <Search
                    className={cn('size-4 shrink-0', open ? 'text-primary' : 'opacity-80')}
                    aria-hidden
                />
                <input
                    id={inputId}
                    type="search"
                    role="combobox"
                    aria-expanded={showPanel}
                    aria-controls={showPanel ? listId : undefined}
                    aria-autocomplete="list"
                    placeholder="جستجو در دوره‌ها و کتاب‌ها…"
                    value={query}
                    autoComplete="off"
                    className={inputClass}
                    onFocus={() => setOpen(true)}
                    onClick={() => setOpen(true)}
                    onChange={(event) => {
                        setQuery(event.target.value);
                        setOpen(true);
                        setActiveIndex(-1);
                    }}
                    onKeyDown={onKeyDown}
                />
            </div>
            {panel}
        </div>
    );
}
