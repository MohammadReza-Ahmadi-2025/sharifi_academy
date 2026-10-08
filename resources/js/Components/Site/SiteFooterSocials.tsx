import type { PageProps } from '@/types';

export type SiteSocialLink = {
    id: number;
    type: string;
    url: string;
    label: string;
    target_blank: boolean;
};

export function siteSocialsFromPage(props: PageProps): SiteSocialLink[] {
    return (props.siteSocials ?? []) as SiteSocialLink[];
}

/** @deprecated استفاده در SiteFooterContactCard */
export function SiteFooterSocials() {
    return null;
}
