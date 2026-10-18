import { CATALOG_SWIPER_SLIDES_PER_VIEW_LG } from '@/Components/Admin/Home/home-section-templates';

export const CATALOG_SLIDES_PER_VIEW_MIN = 2;
export const CATALOG_SLIDES_PER_VIEW_MAX = 6;

export function resolveCatalogSlidesPerViewDesktop(settings?: Record<string, unknown>): number {
    const raw = settings?.catalog_slides_per_view;
    const parsed =
        typeof raw === 'number'
            ? raw
            : typeof raw === 'string' && raw.trim() !== ''
              ? Number.parseInt(raw, 10)
              : Number.NaN;

    if (
        Number.isFinite(parsed) &&
        parsed >= CATALOG_SLIDES_PER_VIEW_MIN &&
        parsed <= CATALOG_SLIDES_PER_VIEW_MAX
    ) {
        return parsed;
    }

    return CATALOG_SWIPER_SLIDES_PER_VIEW_LG;
}
