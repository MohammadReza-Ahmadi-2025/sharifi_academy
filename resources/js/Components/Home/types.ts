export type HomeCatalogItem = {
    id: number;
    title: string;
    slug?: string;
    description?: string | null;
    subtitle?: string | null;
    price?: number;
    image_url?: string | null;
    is_featured?: boolean;
    published_at?: string | null;
    button_text?: string | null;
    button_link?: string | null;
};

export type HomeSectionTab = {
    id: number;
    title: string;
    content_type: string;
    title_url?: string | null;
    view_all_url?: string | null;
    items: HomeCatalogItem[];
};

export type HomeSection = {
    id: number;
    title: string;
    settings?: Record<string, unknown>;
    tabs: HomeSectionTab[];
};
