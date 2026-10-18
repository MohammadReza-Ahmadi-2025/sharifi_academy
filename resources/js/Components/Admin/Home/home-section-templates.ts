import {
    isCatalogTabContentType,
    sanitizeCatalogTabContentType,
} from '@/Components/Admin/Home/home-section-enums';

export type HomeSectionTemplateId =
    | 'hero_split'
    | 'catalog'
    | 'features'
    | 'banners'
    | 'testimonials'
    | 'instructors';

export type TestimonialRow = {
    name: string;
    role: string;
    quote: string;
    rating: number;
};

export const TESTIMONIALS_MAX = 12;

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

export function defaultSampleTestimonials(): TestimonialRow[] {
    return [
        {
            name: 'امیر حسینی',
            role: 'مهندس طراحی صنعتی',
            quote: 'دوره CATIA آکادمی شریفی کاملاً پروژه‌محور بود؛ بعد از چند جلسه توانستم مدل سه‌بعدی قطعه واقعی کارگاه را خودم بسازم.',
            rating: 5,
        },
        {
            name: 'سارا محمدی',
            role: 'کارشناس شبیه‌سازی CFD',
            quote: 'جلسات زنده Adobe Connect و پشتیبانی استاد باعث شد حتی با شغل تمام‌وقت، مسیر یادگیری را از دست ندهم.',
            rating: 5,
        },
        {
            name: 'رضا کریمی',
            role: 'فارغ‌التحصیل تحلیل مهندسی',
            quote: 'سرفصل‌ها منظم است؛ از تمرین‌های هفتگی تا فایل‌های مرجع، همه چیز برای ورود به بازار کار آماده شده.',
            rating: 4,
        },
        {
            name: 'نرگس احمدی',
            role: 'مدیر فنی تیم طراحی',
            quote: 'برای تیم خودمان چند دوره ثبت‌نام کردیم؛ کیفیت تدریس و عمق مثال‌های صنعتی از انتظارمان بالاتر بود.',
            rating: 5,
        },
        {
            name: 'حسین مرادی',
            role: 'طراح CAD',
            quote: 'پنل دانشجو ساده است؛ لینک کلاس، جزوه و تمرین‌ها یکجا هست و نیازی به پیگیری پراکنده نیست.',
            rating: 4,
        },
        {
            name: 'مریم علوی',
            role: 'دانشجوی ارشد مکانیک',
            quote: 'آکادمی شریفی پل ارتباط تئوری دانشگاه با نرم‌افزارهایی بود که در صنعت واقعاً استفاده می‌شود.',
            rating: 5,
        },
    ];
}

export function defaultTestimonialsTab(): HomeSectionTabFormShape {
    return {
        title: 'default',
        content_type: 'testimonials',
        source_type: 'section',
        limit: 0,
        sort_order: 0,
        is_active: true,
        settings: { testimonials: defaultSampleTestimonials() },
        manual_items: [],
        manual_item_ids: [],
        banners: [],
    };
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
    {
        id: 'testimonials',
        label: 'نظر کاربران',
        description: 'کارت نظر با نام، نقش و متن — مناسب معرفی آکادمی',
    },
    {
        id: 'instructors',
        label: 'اساتید',
        description: 'کارت استاد با عکس؛ با کلیک، رزومه در پنجره نمایش داده می‌شود',
    },
];

export function defaultInstructorsTab(): HomeSectionTabFormShape {
    return {
        title: 'default',
        content_type: 'instructor',
        source_type: 'latest',
        limit: 12,
        sort_order: 0,
        is_active: true,
        settings: {},
        manual_items: [],
        manual_item_ids: [],
        banners: [],
    };
}

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
    if (
        explicit === 'hero_split' ||
        explicit === 'catalog' ||
        explicit === 'features' ||
        explicit === 'banners' ||
        explicit === 'testimonials' ||
        explicit === 'instructors'
    ) {
        return explicit;
    }

    const first = tabs[0]?.content_type;
    if (settings?.hero) {
        return 'hero_split';
    }
    if (first === 'testimonials') {
        return 'testimonials';
    }
    if (first === 'instructor') {
        return 'instructors';
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

    if (template === 'instructors') {
        const instructorTabs = current.tabs.filter((t) => t.content_type === 'instructor');

        return {
            settings: {
                ...current.settings,
                section_template: 'instructors',
                catalog_slides_per_view:
                    typeof current.settings.catalog_slides_per_view === 'number'
                        ? current.settings.catalog_slides_per_view
                        : CATALOG_SWIPER_SLIDES_PER_VIEW_LG,
            },
            tabs: instructorTabs.length > 0
                ? instructorTabs.map((tab) => ({
                      ...tab,
                      content_type: 'instructor',
                      limit: tab.limit > 0 ? tab.limit : 12,
                  }))
                : [defaultInstructorsTab()],
        };
    }

    if (template === 'testimonials') {
        const testimonialTabs = current.tabs.filter((t) => t.content_type === 'testimonials');

        return {
            settings: {
                ...current.settings,
                section_template: 'testimonials',
            },
            tabs: testimonialTabs.length > 0
                ? testimonialTabs.map((tab) => ({
                      ...tab,
                      content_type: 'testimonials',
                      source_type: 'section',
                      limit: 0,
                      settings: {
                          ...(tab.settings ?? {}),
                          testimonials: Array.isArray(tab.settings?.testimonials)
                              ? (tab.settings.testimonials as TestimonialRow[])
                              : defaultSampleTestimonials(),
                      },
                  }))
                : [defaultTestimonialsTab()],
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
            catalog_slides_per_view:
                typeof current.settings.catalog_slides_per_view === 'number'
                    ? current.settings.catalog_slides_per_view
                    : CATALOG_SWIPER_SLIDES_PER_VIEW_LG,
        },
        tabs: catalogTabs,
    };
}

export function sectionTemplateId(
    settings?: Record<string, unknown>,
    tabs?: { content_type?: string }[],
): HomeSectionTemplateId {
    const raw = settings?.section_template;
    if (
        raw === 'hero_split' ||
        raw === 'catalog' ||
        raw === 'features' ||
        raw === 'banners' ||
        raw === 'testimonials' ||
        raw === 'instructors'
    ) {
        return raw;
    }

    if (tabs?.length) {
        return inferSectionTemplate(settings, tabs);
    }

    return 'catalog';
}
