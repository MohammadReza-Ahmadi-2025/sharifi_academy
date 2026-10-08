import { categoryFilterQueryParams } from '@/Components/Catalog/category-filter-params';
import { router } from '@inertiajs/react';
import { Search, SlidersHorizontal, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

export type CategoryBrowseFilterState = {
    q: string;
    level?: string;
    featured?: boolean;
    brand?: string;
    sort: string;
    per_page?: number;
};

type Option = { value: string; label: string };

type PanelProps = {
    basePath: string;
    listing: 'courses' | 'products';
    filters: CategoryBrowseFilterState;
    levelOptions?: Option[];
    brandOptions?: Option[];
    sortOptions: Option[];
    onApplied?: () => void;
};

function activeFilterCount(filters: CategoryBrowseFilterState, listing: 'courses' | 'products'): number {
    let n = 0;
    if (filters.q.trim()) n++;
    if (filters.sort !== 'newest') n++;
    if (listing === 'courses' && filters.level) n++;
    if (listing === 'courses' && filters.featured) n++;
    if (listing === 'products' && filters.brand) n++;
    return n;
}

function CategoryFilterPanel({
    basePath,
    listing,
    filters,
    levelOptions = [],
    brandOptions = [],
    sortOptions,
    onApplied,
}: PanelProps) {
    const [q, setQ] = useState(filters.q);

    useEffect(() => {
        setQ(filters.q);
    }, [filters.q]);

    const apply = useCallback(
        (patch: Partial<CategoryBrowseFilterState>) => {
            const next = { ...filters, ...patch };
            router.get(basePath, categoryFilterQueryParams(next, listing), {
                preserveState: true,
                preserveScroll: false,
                replace: true,
                only: ['items', 'filters'],
                onFinish: () => onApplied?.(),
            });
        },
        [basePath, listing, filters, onApplied],
    );

    const submitSearch = (e: React.FormEvent) => {
        e.preventDefault();
        apply({ q });
    };

    const searchPlaceholder =
        listing === 'courses' ? 'نام یا توضیح دوره...' : 'نام یا کد محصول...';

    const hasActive = activeFilterCount(filters, listing) > 0;

    return (
        <div className="space-y-6">
            <form onSubmit={submitSearch}>
                <p className="mb-2 text-xs font-bold text-neutral-black/50">جستجو</p>
                <div className="relative">
                    <Search className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-neutral-black/35" />
                    <input
                        type="search"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder={searchPlaceholder}
                        className="w-full rounded-xl border border-secondary/90 bg-surface-muted/40 py-2.5 pr-10 pl-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/15"
                    />
                </div>
                <button
                    type="submit"
                    className="mt-2 w-full rounded-xl border border-secondary/90 bg-neutral-white py-2 text-sm font-semibold text-neutral-black transition hover:border-primary/40 hover:text-primary"
                >
                    اعمال جستجو
                </button>
            </form>

            <div>
                <p className="mb-2 text-xs font-bold text-neutral-black/50">مرتب‌سازی</p>
                <ul className="space-y-1">
                    {sortOptions.map((opt) => {
                        const active = filters.sort === opt.value;
                        return (
                            <li key={opt.value}>
                                <button
                                    type="button"
                                    onClick={() => apply({ sort: opt.value })}
                                    className={`flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold transition ${
                                        active
                                            ? 'bg-primary text-on-primary shadow-sm'
                                            : 'text-neutral-black/75 hover:bg-surface-muted'
                                    }`}
                                >
                                    {opt.label}
                                    {active && <span className="size-1.5 rounded-full bg-on-primary" />}
                                </button>
                            </li>
                        );
                    })}
                </ul>
            </div>

            {listing === 'courses' && levelOptions.length > 0 && (
                <div>
                    <p className="mb-2 text-xs font-bold text-neutral-black/50">سطح</p>
                    <ul className="space-y-1">
                        <li>
                            <button
                                type="button"
                                onClick={() => apply({ level: '' })}
                                className={`w-full rounded-xl px-3 py-2 text-sm font-medium transition ${
                                    !filters.level ? 'bg-surface-muted font-bold text-primary' : 'text-neutral-black/70 hover:bg-surface-muted/80'
                                }`}
                            >
                                همه
                            </button>
                        </li>
                        {levelOptions.map((opt) => {
                            const active = filters.level === opt.value;
                            return (
                                <li key={opt.value}>
                                    <button
                                        type="button"
                                        onClick={() => apply({ level: opt.value })}
                                        className={`w-full rounded-xl px-3 py-2 text-sm font-medium transition ${
                                            active ? 'bg-surface-muted font-bold text-primary' : 'text-neutral-black/70 hover:bg-surface-muted/80'
                                        }`}
                                    >
                                        {opt.label}
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            )}

            {listing === 'products' && brandOptions.length > 0 && (
                <div>
                    <p className="mb-2 text-xs font-bold text-neutral-black/50">برند</p>
                    <ul className="max-h-48 space-y-1 overflow-y-auto pe-1">
                        <li>
                            <button
                                type="button"
                                onClick={() => apply({ brand: '' })}
                                className={`w-full rounded-xl px-3 py-2 text-sm font-medium transition ${
                                    !filters.brand ? 'bg-surface-muted font-bold text-primary' : 'text-neutral-black/70 hover:bg-surface-muted/80'
                                }`}
                            >
                                همه برندها
                            </button>
                        </li>
                        {brandOptions.map((opt) => {
                            const active = filters.brand === opt.value;
                            return (
                                <li key={opt.value}>
                                    <button
                                        type="button"
                                        onClick={() => apply({ brand: opt.value })}
                                        className={`w-full rounded-xl px-3 py-2 text-sm font-medium text-start transition ${
                                            active ? 'bg-surface-muted font-bold text-primary' : 'text-neutral-black/70 hover:bg-surface-muted/80'
                                        }`}
                                    >
                                        {opt.label}
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            )}

            {listing === 'courses' && (
                <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-secondary/80 bg-surface-muted/30 px-3 py-3">
                    <input
                        type="checkbox"
                        checked={Boolean(filters.featured)}
                        onChange={(e) => apply({ featured: e.target.checked })}
                        className="size-4 rounded border-secondary text-primary focus:ring-primary"
                    />
                    <span className="text-sm font-semibold text-neutral-black">فقط دوره‌های ویژه</span>
                </label>
            )}

            {hasActive && (
                <button
                    type="button"
                    onClick={() => {
                        setQ('');
                        apply({
                            q: '',
                            level: '',
                            featured: false,
                            brand: '',
                            sort: 'newest',
                        });
                    }}
                    className="flex w-full items-center justify-center gap-1.5 rounded-xl py-2 text-sm font-semibold text-primary hover:bg-primary/5"
                >
                    <X className="size-4" />
                    حذف همه فیلترها
                </button>
            )}
        </div>
    );
}

type LayoutProps = PanelProps & {
    totalCount: number;
    itemsLabel: string;
};

/** سایدبار دسکتاپ — داخل ستون چپ صفحه دسته */
export function CategoryFilterSidebar(props: LayoutProps) {
    return (
        <div className="rounded-2xl border border-secondary/80 bg-neutral-white p-5 shadow-sm">
            <div className="mb-5 flex items-center gap-2 border-b border-secondary/70 pb-4">
                <SlidersHorizontal className="size-4 text-primary" />
                <span className="text-sm font-extrabold text-neutral-black">فیلتر نتایج</span>
            </div>
            <CategoryFilterPanel {...props} />
        </div>
    );
}

/** نوار موبایل + دراور پایین */
export function CategoryFilterMobile({ totalCount, itemsLabel, ...panelProps }: LayoutProps) {
    const [open, setOpen] = useState(false);
    const count = activeFilterCount(panelProps.filters, panelProps.listing);

    useEffect(() => {
        if (!open) return;
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setOpen(false);
        };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [open]);

    return (
        <>
            <div className="mb-5 flex items-center justify-between gap-3 rounded-2xl border border-secondary/80 bg-neutral-white px-4 py-3 shadow-sm lg:hidden">
                <p className="text-sm text-neutral-black/65">
                    <span className="font-extrabold text-neutral-black">{totalCount.toLocaleString('fa-IR')}</span>
                    {' '}
                    {itemsLabel}
                </p>
                <button
                    type="button"
                    onClick={() => setOpen(true)}
                    className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-bold text-on-primary shadow-sm"
                >
                    <SlidersHorizontal className="size-4" />
                    فیلتر
                    {count > 0 && (
                        <span className="flex size-5 items-center justify-center rounded-full bg-on-primary/20 text-[11px]">
                            {count.toLocaleString('fa-IR')}
                        </span>
                    )}
                </button>
            </div>

            {open && (
                <div className="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
                    <button
                        type="button"
                        className="absolute inset-0 bg-neutral-black/40 backdrop-blur-[2px]"
                        aria-label="بستن"
                        onClick={() => setOpen(false)}
                    />
                    <div className="absolute inset-x-0 bottom-0 max-h-[min(88vh,640px)] overflow-hidden rounded-t-3xl border border-secondary/80 bg-neutral-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-secondary/70 px-5 py-4">
                            <span className="font-extrabold text-neutral-black">فیلتر و جستجو</span>
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="rounded-full p-2 text-neutral-black/60 hover:bg-surface-muted"
                                aria-label="بستن"
                            >
                                <X className="size-5" />
                            </button>
                        </div>
                        <div className="overflow-y-auto px-5 py-5 pb-8">
                            <CategoryFilterPanel {...panelProps} />
                        </div>
                        <div className="border-t border-secondary/70 px-5 py-4">
                            <button
                                type="button"
                                onClick={() => setOpen(false)}
                                className="w-full rounded-xl bg-primary py-3 text-sm font-bold text-on-primary"
                            >
                                مشاهده نتایج
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
