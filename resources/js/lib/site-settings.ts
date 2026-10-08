import type { PageProps } from '@/types';

export type SiteSettings = {
    site_name?: string | null;
    site_description?: string | null;
    site_logo?: string | null;
    site_icon?: string | null;
    site_color?: string | null;
    maintenance_mode?: boolean;
    contact_email?: string | null;
    contact_phone?: string | null;
    address?: string | null;
    seo_meta_title?: string | null;
    seo_meta_description?: string | null;
    seo_meta_keywords?: string | null;
    seo_og_image?: string | null;
    google_analytics_id?: string | null;
    google_search_console?: string | null;
    home_hero_badge?: string | null;
    home_hero_title?: string | null;
    home_hero_subtitle?: string | null;
    home_hero_cta_primary?: string | null;
    home_hero_cta_primary_url?: string | null;
    home_hero_cta_secondary?: string | null;
    home_hero_cta_secondary_url?: string | null;
};

export function siteSettingsFromPage(props: PageProps): SiteSettings {
    return (props.siteSettings ?? {}) as SiteSettings;
}

export function hexToRgbTriplet(hex: string): string | null {
    const normalized = hex.trim();
    if (!/^#[0-9A-Fa-f]{6}$/.test(normalized)) {
        return null;
    }

    const r = parseInt(normalized.slice(1, 3), 16);
    const g = parseInt(normalized.slice(3, 5), 16);
    const b = parseInt(normalized.slice(5, 7), 16);

    return `${r} ${g} ${b}`;
}

export function darkerRgbTriplet(triplet: string, factor = 0.82): string {
    const parts = triplet.split(/\s+/).map((n) => Math.max(0, Math.round(Number(n) * factor)));

    return parts.join(' ');
}
