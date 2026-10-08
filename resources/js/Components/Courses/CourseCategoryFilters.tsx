import { router } from '@inertiajs/react';
import { Search, SlidersHorizontal } from 'lucide-react';
import { useCallback, useState } from 'react';

export type CourseCategoryFilterState = {
    q: string;
    level: string;
    featured: boolean;
    sort: string;
    per_page: number;
};

type LevelOption = { value: string; label: string };
type SortOption = { value: string; label: string };

type Props = {
    basePath: string;
    filters: CourseCategoryFilterState;
    levelOptions: LevelOption[];
    sortOptions: SortOption[];
};

export function CourseCategoryFilters({ basePath, filters, levelOptions, sortOptions }: Props) {
    const [q, setQ] = useState(filters.q);

    const apply = useCallback(
        (patch: Partial<CourseCategoryFilterState>) => {
            const next = { ...filters, ...patch };
            const params: Record<string, string> = {};

            if (next.q.trim()) {
                params.q = next.q.trim();
            }
            if (next.level) {
                params.level = next.level;
            }
            if (next.featured) {
                params.featured = '1';
            }
            if (next.sort && next.sort !== 'newest') {
                params.sort = next.sort;
            }
            if (next.per_page !== 12) {
                params.per_page = String(next.per_page);
            }

            router.get(basePath, params, { preserveState: true, preserveScroll: true, replace: true });
        },
        [basePath, filters],
    );

    const submitSearch = (e: React.FormEvent) => {
        e.preventDefault();
        apply({ q });
    };

    return (
        <div className="mb-8 rounded-2xl border border-secondary bg-neutral-white p-4 shadow-sm sm:p-5">
            <div className="mb-4 flex items-center gap-2 text-sm font-bold text-neutral-black">
                <SlidersHorizontal className="size-4 text-primary" />
                فیلتر و مرتب‌سازی
            </div>

            <form onSubmit={submitSearch} className="mb-4 flex flex-col gap-3 sm:flex-row">
                <div className="relative flex-1">
                    <Search className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-neutral-black/40" />
                    <input
                        type="search"
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder="جستجو در عنوان یا توضیح دوره..."
                        className="w-full rounded-xl border border-secondary bg-surface-muted/40 py-2.5 pr-10 pl-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/15"
                    />
                </div>
                <button
                    type="submit"
                    className="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-on-primary shadow-sm transition hover:opacity-95"
                >
                    اعمال
                </button>
            </form>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label className="block text-xs font-semibold text-neutral-black/60">
                    سطح
                    <select
                        value={filters.level}
                        onChange={(e) => apply({ level: e.target.value })}
                        className="mt-1.5 w-full rounded-xl border border-secondary bg-white px-3 py-2 text-sm text-neutral-black"
                    >
                        <option value="">همه سطوح</option>
                        {levelOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                </label>

                <label className="block text-xs font-semibold text-neutral-black/60">
                    مرتب‌سازی
                    <select
                        value={filters.sort}
                        onChange={(e) => apply({ sort: e.target.value })}
                        className="mt-1.5 w-full rounded-xl border border-secondary bg-white px-3 py-2 text-sm text-neutral-black"
                    >
                        {sortOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                </label>

                <label className="block text-xs font-semibold text-neutral-black/60">
                    در هر صفحه
                    <select
                        value={String(filters.per_page)}
                        onChange={(e) => apply({ per_page: Number(e.target.value) })}
                        className="mt-1.5 w-full rounded-xl border border-secondary bg-white px-3 py-2 text-sm text-neutral-black"
                    >
                        <option value="12">۱۲</option>
                        <option value="24">۲۴</option>
                        <option value="36">۳۶</option>
                    </select>
                </label>

                <label className="flex cursor-pointer items-end gap-2 rounded-xl border border-secondary bg-surface-muted/30 px-3 py-2.5 sm:mt-5">
                    <input
                        type="checkbox"
                        checked={filters.featured}
                        onChange={(e) => apply({ featured: e.target.checked })}
                        className="size-4 rounded border-secondary text-primary focus:ring-primary"
                    />
                    <span className="text-sm font-medium text-neutral-black">فقط دوره‌های ویژه</span>
                </label>
            </div>

            {(filters.q || filters.level || filters.featured || filters.sort !== 'newest') && (
                <button
                    type="button"
                    onClick={() => {
                        setQ('');
                        apply({ q: '', level: '', featured: false, sort: 'newest', per_page: 12 });
                    }}
                    className="mt-4 text-sm font-semibold text-primary hover:underline"
                >
                    پاک کردن فیلترها
                </button>
            )}
        </div>
    );
}
