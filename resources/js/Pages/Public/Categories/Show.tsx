import {
    CategoryFilterMobile,
    CategoryFilterSidebar,
    type CategoryBrowseFilterState,
} from '@/Components/Catalog/CategoryBrowseFilters';
import { categoryFilterQueryParams } from '@/Components/Catalog/category-filter-params';
import { ProductCatalogGrid, type ProductCatalogCard } from '@/Components/Catalog/ProductCatalogGrid';
import { CourseCatalogGrid, type CourseCatalogCard } from '@/Components/Courses/CourseCatalogGrid';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { CategoryPageHeader } from '@/Components/Catalog/CategoryPageHeader';
import AppLayout from '@/Layouts/AppLayout';
import { Link, router } from '@inertiajs/react';
import { FolderTree, Loader2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

type Subcategory = { id: number; title: string; slug: string; items_count: number };

type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    category: {
        id: number;
        title: string;
        slug: string;
        description: string | null;
        image_url: string | null;
        seo: CatalogSeo;
    };
    listing: 'courses' | 'products';
    items_label: string;
    items: Paginator<CourseCatalogCard | ProductCatalogCard>;
    subcategories: Subcategory[];
    filters: CategoryBrowseFilterState;
    filterOptions: {
        levels: { value: string; label: string }[];
        sorts: { value: string; label: string }[];
        brands: { value: string; label: string }[];
    };
    structuredData: {
        breadcrumb: Record<string, unknown>;
        webPage: Record<string, unknown>;
        itemList: Record<string, unknown>;
    };
};

export default function CategoryShow({
    category,
    listing,
    items_label,
    items,
    subcategories,
    filters,
    filterOptions,
    structuredData,
}: Props) {
    const basePath = `/categories/${category.slug}`;
    const filterSignature = useMemo(
        () =>
            JSON.stringify({
                q: filters.q,
                level: filters.level,
                featured: filters.featured,
                brand: filters.brand,
                sort: filters.sort,
            }),
        [filters.q, filters.level, filters.featured, filters.brand, filters.sort],
    );

    const [displayItems, setDisplayItems] = useState(items.data);
    const [loadingMore, setLoadingMore] = useState(false);

    useEffect(() => {
        if (items.current_page <= 1) {
            setDisplayItems(items.data);
            return;
        }

        setDisplayItems((prev) => {
            const seen = new Set(prev.map((row) => row.id));
            const appended = items.data.filter((row) => !seen.has(row.id));
            return appended.length > 0 ? [...prev, ...appended] : prev;
        });
    }, [items.data, items.current_page, filterSignature]);

    const hasMore = items.current_page < items.last_page;

    const loadMore = () => {
        if (!hasMore || loadingMore) {
            return;
        }

        setLoadingMore(true);
        const params = categoryFilterQueryParams(filters, listing, {
            page: String(items.current_page + 1),
        });

        router.get(basePath, params, {
            preserveState: true,
            preserveScroll: true,
            only: ['items'],
            onFinish: () => setLoadingMore(false),
        });
    };

    return (
        <AppLayout>
            <SiteDefaultHead title={category.seo.title} description={category.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={category.seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage, structuredData.itemList]}
            />

            <div className="mx-auto max-w-[88rem] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <CategoryPageHeader
                    title={category.title}
                    imageUrl={category.image_url}
                    total={items.total}
                    itemsLabel={items_label}
                    description={category.description}
                />
                {subcategories.length > 0 && (
                    <section className="mb-8" aria-label="زیردسته‌ها">
                        <div className="mb-3 flex items-center gap-2 text-sm font-bold text-neutral-black">
                            <FolderTree className="size-4 text-primary" />
                            زیردسته‌ها
                        </div>
                        <div className="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 scrollbar-thin">
                            {subcategories.map((sub) => (
                                <Link
                                    key={sub.id}
                                    href={`/categories/${sub.slug}`}
                                    className="shrink-0 rounded-2xl border border-secondary/80 bg-neutral-white px-4 py-2.5 text-sm font-semibold text-neutral-black/80 shadow-sm transition hover:border-primary/40 hover:text-primary"
                                >
                                    {sub.title}
                                    <span className="ms-1.5 text-xs font-bold text-primary/80">
                                        {sub.items_count.toLocaleString('fa-IR')}
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                <div className="lg:grid lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] lg:items-start lg:gap-10">
                    <aside className="hidden lg:block lg:sticky lg:top-24 lg:self-start">
                        <CategoryFilterSidebar
                            basePath={basePath}
                            listing={listing}
                            filters={filters}
                            levelOptions={filterOptions.levels}
                            brandOptions={filterOptions.brands}
                            sortOptions={filterOptions.sorts}
                            totalCount={items.total}
                            itemsLabel={items_label}
                        />
                    </aside>

                    <div className="min-w-0">
                        <CategoryFilterMobile
                            basePath={basePath}
                            listing={listing}
                            filters={filters}
                            levelOptions={filterOptions.levels}
                            brandOptions={filterOptions.brands}
                            sortOptions={filterOptions.sorts}
                            totalCount={items.total}
                            itemsLabel={items_label}
                        />

                        <p className="mb-5 hidden text-sm text-neutral-black/55 lg:block">
                            <span className="font-extrabold text-neutral-black">
                                {items.total.toLocaleString('fa-IR')}
                            </span>
                            {' '}
                            {items_label}
                        </p>

                        {listing === 'courses' ? (
                            <CourseCatalogGrid courses={displayItems as CourseCatalogCard[]} variant="category" />
                        ) : (
                            <ProductCatalogGrid products={displayItems as ProductCatalogCard[]} variant="category" />
                        )}

                        {hasMore && (
                            <div className="mt-10 flex justify-center">
                                <button
                                    type="button"
                                    onClick={loadMore}
                                    disabled={loadingMore}
                                    className="inline-flex min-w-[12rem] items-center justify-center gap-2 rounded-2xl bg-primary px-8 py-3.5 text-sm font-bold text-on-primary shadow-md shadow-primary/20 transition hover:opacity-95 disabled:opacity-70"
                                >
                                    {loadingMore ? (
                                        <>
                                            <Loader2 className="size-4 animate-spin" />
                                            در حال بارگذاری...
                                        </>
                                    ) : (
                                        <>نمایش بیشتر</>
                                    )}
                                </button>
                            </div>
                        )}

                        {displayItems.length > 0 && !hasMore && items.last_page > 1 && (
                            <p className="mt-8 text-center text-xs font-medium text-neutral-black/45">
                                همه {items.total.toLocaleString('fa-IR')} مورد نمایش داده شد
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
