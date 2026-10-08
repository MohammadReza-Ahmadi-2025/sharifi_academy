import { darkerRgbTriplet, hexToRgbTriplet, siteSettingsFromPage } from '@/lib/site-settings';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

export function SiteTheme() {
    const props = usePage<PageProps>().props;
    const color = siteSettingsFromPage(props).site_color;

    useEffect(() => {
        const root = document.documentElement;

        if (!color) {
            return;
        }

        const triplet = hexToRgbTriplet(color);
        if (!triplet) {
            return;
        }

        root.style.setProperty('--color-primary-rgb', triplet);
        root.style.setProperty('--color-primary-hover', `rgb(${darkerRgbTriplet(triplet)})`);
        root.style.setProperty('--primary', triplet);
    }, [color]);

    return null;
}
