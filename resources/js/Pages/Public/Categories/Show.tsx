import {
    CategoryFilterMobile,
    CategoryFilterSidebar,
    type CategoryBrowseFilterState,
    type CategoryPriceBounds,
} from '@/Components/Catalog/CategoryBrowseFilters';
import { categoryFilterQueryParams } from '@/Components/Catalog/category-filter-params';
import { CategoryPageHeader } from '@/Components/Catalog/CategoryPageHeader';
import {
    CategorySubcategoryGrid,
    type CategorySubcategoryCard,
} from '@/Components/Catalog/CategorySubcategoryGrid';
import { ProductCatalogGrid, type ProductCatalogCard } from '@/Components/Catalog/ProductCatalogGrid';
import { CourseCatalogGrid, type CourseCatalogCard } from '@/Components/Courses/CourseCatalogGrid';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { router } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

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
    subcategories: CategorySubcategoryCard[];
    filters: CategoryBrowseFilterState;
    filterOptions: {
        levels: { value: string; label: string }[];
        sorts: { value: string; label: string }[];
        brands: { value: string; label: string }[];
        delivery_types: { value: string; label: string }[];
        price_bounds: CategoryPriceBounds;
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
                delivery: filters.delivery,
                featured: filters.featured,
                brand: filters.brand,
                price_min: filters.price_min,
                price_max: filters.price_max,
                sort: filters.sort,
            }),
        [
            filters.q,
            filters.level,
            filters.delivery,
            filters.featured,
            filters.brand,
            filters.price_min,
            filters.price_max,
            filters.sort,
        ],
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

    const filterProps = {
        basePath,
        listing,
        filters,
        levelOptions: filterOptions.levels,
        deliveryOptions: filterOptions.delivery_types,
        brandOptions: filterOptions.brands,
        sortOptions: filterOptions.sorts,
        priceBounds: filterOptions.price_bounds,
        totalCount: items.total,
        itemsLabel: items_label,
    };

    return (
        <AppLayout fullWidth mainClassName="bg-surface-muted">
            <SiteDefaultHead title={category.seo.title} description={category.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={category.seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage, structuredData.itemList]}
            />

            <div className="category-browse-page min-h-full">
                <CategoryPageHeader
                    title={category.title}
                    imageUrl={category.image_url}
                    total={items.total}
                    itemsLabel={items_label}
                    description={category.description}
                />

                <CategorySubcategoryGrid subcategories={subcategories} itemsLabel={items_label} />

                <section className="relative">
                    <div
                        className="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-neutral-white to-transparent"
                        aria-hidden
                    />

                    <div className="relative mx-auto max-w-[88rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
                        <div className="lg:grid lg:grid-cols-[minmax(0,17.5rem)_minmax(0,1fr)] lg:items-start lg:gap-8 xl:gap-10">
                            <aside className="hidden lg:block lg:sticky lg:top-24 lg:self-start">
                                <CategoryFilterSidebar {...filterProps} />
                            </aside>

                            <div className="min-w-0">
                                <CategoryFilterMobile {...filterProps} />

                                <div className="mb-6 hidden items-center justify-between gap-3 lg:flex">
                                    <div>
                                        <h2 className="text-lg font-extrabold text-neutral-black">
                                            {listing === 'courses' ? 'دوره‌ها' : 'محصولات'}
                                        </h2>
                                        <p className="mt-0.5 text-sm text-neutral-black/50">
                                            <span className="font-bold text-neutral-black/75">
                                                {items.total.toLocaleString('fa-IR')}
                                            </span>
                                            {' '}
                                            {items_label} در این دسته
                                        </p>
                                    </div>
                                </div>

                                <div className="rounded-[1.75rem] border border-secondary/60 bg-neutral-white/80 p-3 shadow-[0_20px_50px_-28px_rgba(0,40,90,0.2)] backdrop-blur-sm sm:p-5 lg:p-6">
                                    {listing === 'courses' ? (
                                        <CourseCatalogGrid
                                            courses={displayItems as CourseCatalogCard[]}
                                            variant="category"
                                        />
                                    ) : (
                                        <ProductCatalogGrid
                                            products={displayItems as ProductCatalogCard[]}
                                            variant="category"
                                        />
                                    )}
                                </div>

                                {hasMore ? (
                                    <div className="mt-10 flex justify-center">
                                        <button
                                            type="button"
                                            onClick={loadMore}
                                            disabled={loadingMore}
                                            className="inline-flex min-w-[13rem] items-center justify-center gap-2 rounded-full bg-primary px-8 py-3.5 text-sm font-bold text-on-primary shadow-lg shadow-primary/25 transition hover:opacity-95 disabled:opacity-70"
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
                                ) : null}

                                {displayItems.length > 0 && !hasMore && items.last_page > 1 ? (
                                    <p className="mt-8 text-center text-xs font-medium text-neutral-black/40">
                                        همه {items.total.toLocaleString('fa-IR')} مورد نمایش داده شد
                                    </p>
                                ) : null}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
