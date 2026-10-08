import type { CategoryBrowseFilterState } from '@/Components/Catalog/CategoryBrowseFilters';

export function categoryFilterQueryParams(
    filters: CategoryBrowseFilterState,
    listing: 'courses' | 'products',
    extra?: Record<string, string>,
): Record<string, string> {
    const params: Record<string, string> = { ...extra };

    if (filters.q.trim()) {
        params.q = filters.q.trim();
    }
    if (listing === 'courses' && filters.level) {
        params.level = filters.level;
    }
    if (listing === 'courses' && filters.featured) {
        params.featured = '1';
    }
    if (listing === 'products' && filters.brand) {
        params.brand = filters.brand;
    }
    if (filters.sort && filters.sort !== 'newest') {
        params.sort = filters.sort;
    }

    return params;
}
