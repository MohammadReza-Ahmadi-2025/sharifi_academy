import type { HomeSectionTemplateId } from '@/Components/Admin/Home/home-section-templates';

/** انواع مجاز برای تب‌های قالب «لیست کارت» (اسلایدر) — بدون بنر و بدون گرید سه‌تایی */
export const CATALOG_TAB_CONTENT_TYPE_KEYS = ['product', 'course', 'post', 'brand', 'category'] as const;

export type CatalogTabContentType = (typeof CATALOG_TAB_CONTENT_TYPE_KEYS)[number];

export function isCatalogTabContentType(contentType: string): boolean {
    return (CATALOG_TAB_CONTENT_TYPE_KEYS as readonly string[]).includes(contentType);
}

export function contentTypesForTemplate(
    template: HomeSectionTemplateId,
    all: Record<string, string>,
): Record<string, string> {
    if (template === 'catalog') {
        return Object.fromEntries(
            CATALOG_TAB_CONTENT_TYPE_KEYS.filter((key) => all[key]).map((key) => [key, all[key]]),
        );
    }

    if (template === 'hero_split' || template === 'banners') {
        return all.banner ? { banner: all.banner } : {};
    }

    if (template === 'features') {
        return all.features ? { features: all.features } : {};
    }

    if (template === 'testimonials') {
        return all.testimonials ? { testimonials: all.testimonials } : {};
    }

    if (template === 'instructors') {
        return all.instructor ? { instructor: all.instructor } : {};
    }

    return all;
}

const SOURCE_KEYS_BY_CONTENT_TYPE: Record<string, string[]> = {
    banner: ['manual'],
    features: ['section'],
    testimonials: ['section'],
    instructor: ['latest', 'manual'],
    product: ['latest', 'oldest', 'best_selling', 'most_viewed', 'manual', 'by_category', 'by_brand'],
    course: ['latest', 'oldest', 'most_viewed', 'manual', 'by_category'],
    post: ['latest', 'oldest', 'most_viewed', 'manual'],
    brand: ['latest', 'oldest', 'manual'],
    category: ['latest', 'oldest', 'manual'],
};

export function sourceTypesForContentType(
    contentType: string,
    all: Record<string, string>,
): Record<string, string> {
    const keys = SOURCE_KEYS_BY_CONTENT_TYPE[contentType];
    if (!keys) {
        return Object.fromEntries(
            ['latest', 'oldest', 'manual'].filter((key) => all[key]).map((key) => [key, all[key]]),
        );
    }

    return Object.fromEntries(keys.filter((key) => all[key]).map((key) => [key, all[key]]));
}

export function allowedSourceTypeKeys(contentType: string): string[] {
    return SOURCE_KEYS_BY_CONTENT_TYPE[contentType] ?? ['latest', 'oldest', 'manual'];
}

export function defaultSourceTypeForContentType(contentType: string): string {
    if (contentType === 'banner') {
        return 'manual';
    }
    if (contentType === 'features' || contentType === 'testimonials') {
        return 'section';
    }

    return 'latest';
}

export function sanitizeCatalogTabContentType(contentType: string): CatalogTabContentType {
    if (isCatalogTabContentType(contentType)) {
        return contentType as CatalogTabContentType;
    }

    return 'course';
}
