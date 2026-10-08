import {
    isCatalogTabContentType,
    sanitizeCatalogTabContentType,
} from '@/Components/Admin/Home/home-section-enums';

export type HomeSectionTemplateId = 'hero_split' | 'catalog' | 'features' | 'banners';

/** تعداد کارت‌های قابل‌مشاهده هم‌زمان در عرض بزرگ (اسلایدر) — نه سقف آیتم‌های تب */
export const CATALOG_SWIPER_SLIDES_PER_VIEW_LG = 5;
export const FEATURES_GRID_COLUMNS = 3;

export function defaultFeatureGridRows() {
    return Array.from({ length: FEATURES_GRID_COLUMNS }, () => ({
        label: '',
        text: '',
        image_url: '',
        image: [],
    }));
}

export function defaultFeaturesTab(): HomeSectionTabFormShape {
    return {
        title: 'default',
        content_type: 'features',
        source_type: 'section',
        limit: 0,
        sort_order: 0,
        is_active: true,
        settings: { features: defaultFeatureGridRows() },
        manual_items: [],
        manual_item_ids: [],
        banners: [],
    };
}

export type HomeSectionTemplateMeta = {
    id: HomeSectionTemplateId;
    label: string;
    description: string;
};

export const HOME_SECTION_TEMPLATES: HomeSectionTemplateMeta[] = [
    {
        id: 'hero_split',
        label: 'هیرو کلاسیک',
        description: 'متن و ویژگی‌ها چپ، اسلاید بنر راست (سکشن اول صفحه)',
    },
    {
        id: 'catalog',
        label: 'لیست کارت',
        description: `اسلایدر Swiper — تا ۵۰ آیتم در هر تب (${CATALOG_SWIPER_SLIDES_PER_VIEW_LG} کارت هم‌زمان در دسکتاپ)`,
    },
    {
        id: 'features',
        label: 'گرید سه‌تایی',
        description: 'سه ستون با عنوان، متن و تصویر (اختیاری)',
    },
    {
        id: 'banners',
        label: 'اسلایدر بنر',
        description: 'بنرهای تمام‌عرض با چیدمان انتخابی',
    },
];

export function defaultHeroSettings() {
    return {
        badge: 'آکادمی تخصصی مهندسی',
        title: 'مهارت مهندسی را عملی یاد بگیرید',
        subtitle: '',
        cta_primary: 'مشاهده دوره‌ها',
        cta_primary_url: '/courses',
        cta_secondary: 'ورود / ثبت‌نام',
        cta_secondary_url: '/login',
        highlights: [
            { label: 'کلاس زنده Adobe Connect', text: '' },
            { label: 'کتاب تخصصی PDF', text: '' },
            { label: 'پرداخت امن زرین‌پال', text: '' },
        ],
        show_live_badge: true,
        promo: {
            title: 'کتاب‌های آکادمی',
            text: 'منابع مکتوب هم‌مسیر با کلاس',
            link: '/books',
            image_url: '/banner.png',
        },
    };
}

export type HomeHeroSettingsForm = ReturnType<typeof defaultHeroSettings>;

export type HomeSectionTabFormShape = {
    title: string;
    content_type: string;
    source_type: string;
    limit: number;
    sort_order: number;
    is_active: boolean;
    settings: Record<string, unknown>;
    manual_items: unknown[];
    manual_item_ids: number[];
    banners?: unknown[];
};

export function inferSectionTemplate(
    settings: Record<string, unknown> | undefined,
    tabs: { content_type?: string }[],
): HomeSectionTemplateId {
    const explicit = settings?.section_template;
    if (explicit === 'hero_split' || explicit === 'catalog' || explicit === 'features' || explicit === 'banners') {
        return explicit;
    }

    const first = tabs[0]?.content_type;
    if (settings?.hero) {
        return 'hero_split';
    }
    if (first === 'features') {
        return 'features';
    }
    if (first === 'banner' && tabs.length === 1) {
        return 'banners';
    }

    return 'catalog';
}

export function formStateForTemplate(
    template: HomeSectionTemplateId,
    current: { settings: Record<string, unknown>; tabs: HomeSectionTabFormShape[] },
): { settings: Record<string, unknown>; tabs: HomeSectionTabFormShape[] } {
    const baseTab = (): HomeSectionTabFormShape => ({
        title: 'default',
        content_type: 'course',
        source_type: 'latest',
        limit: 8,
        sort_order: 0,
        is_active: true,
        settings: {},
        manual_items: [],
        manual_item_ids: [],
        banners: [],
    });

    if (template === 'hero_split') {
        const existingBanners = current.tabs.find((t) => t.content_type === 'banner')?.banners ?? [];

        return {
            settings: {
                ...current.settings,
                section_template: 'hero_split',
                hero: current.settings.hero ?? defaultHeroSettings(),
            },
            tabs: [
                {
                    ...baseTab(),
                    content_type: 'banner',
                    source_type: 'manual',
                    banners: existingBanners.length ? existingBanners : [],
                },
            ],
        };
    }

    if (template === 'features') {
        const legacySectionFeatures = current.settings.features;
        const featureTabs = current.tabs.filter((t) => t.content_type === 'features');
        let tabs: HomeSectionTabFormShape[];

        if (featureTabs.length > 0) {
            tabs = featureTabs.map((tab) => ({
                ...tab,
                content_type: 'features',
                source_type: 'section',
                limit: 0,
                settings: {
                    ...(tab.settings ?? {}),
                    features: Array.isArray(tab.settings?.features)
                        ? (tab.settings.features as ReturnType<typeof defaultFeatureGridRows>).slice(
                              0,
                              FEATURES_GRID_COLUMNS,
                          )
                        : defaultFeatureGridRows(),
                },
            }));
        } else {
            const firstTab = defaultFeaturesTab();
            if (Array.isArray(legacySectionFeatures) && legacySectionFeatures.length > 0) {
                firstTab.settings = {
                    features: legacySectionFeatures.slice(0, FEATURES_GRID_COLUMNS) as ReturnType<
                        typeof defaultFeatureGridRows
                    >,
                };
            }
            tabs = [firstTab];
        }

        const { features: _removed, ...settingsWithoutLegacyFeatures } = current.settings;

        return {
            settings: {
                ...settingsWithoutLegacyFeatures,
                section_template: 'features',
            },
            tabs,
        };
    }

    if (template === 'banners') {
        const existingBanners = current.tabs.find((t) => t.content_type === 'banner')?.banners ?? [];

        return {
            settings: {
                ...current.settings,
                section_template: 'banners',
                banner_layout: current.settings.banner_layout ?? 'showcase',
            },
            tabs: [
                {
                    ...baseTab(),
                    content_type: 'banner',
                    source_type: 'manual',
                    banners: existingBanners.length ? existingBanners : [],
                },
            ],
        };
    }

    const catalogTabs =
        current.tabs.length && isCatalogTabContentType(current.tabs[0].content_type)
            ? current.tabs.map((tab) => ({
                  ...tab,
                  content_type: sanitizeCatalogTabContentType(tab.content_type),
                  limit: 12,
                  banners: [],
              }))
            : [{ ...baseTab(), limit: 12 }];

    return {
        settings: {
            ...current.settings,
            section_template: 'catalog',
            layout: 'carousel',
        },
        tabs: catalogTabs,
    };
}

export function sectionTemplateId(
    settings?: Record<string, unknown>,
    tabs?: { content_type?: string }[],
): HomeSectionTemplateId {
    const raw = settings?.section_template;
    if (raw === 'hero_split' || raw === 'catalog' || raw === 'features' || raw === 'banners') {
        return raw;
    }

    if (tabs?.length) {
        return inferSectionTemplate(settings, tabs);
    }

    return 'catalog';
}
