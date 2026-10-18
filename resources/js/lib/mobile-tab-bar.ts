import type { CourseCategoryNavNode, PageProps } from '@/types';

export const MOBILE_COURSE_CATEGORIES_HREF = '#course-categories';

export type MobileTabAction = 'course-categories';

export type MobileTabItem = {
    id: number;
    label: string;
    href: string;
    icon?: string | null;
    targetBlank: boolean;
    action?: MobileTabAction;
};

type SiteMenuGroup = PageProps['siteMenus'][string][number];

const FALLBACK_TABS: MobileTabItem[] = [
    { id: 0, label: 'خانه', href: '/', icon: 'home', targetBlank: false },
    {
        id: 1,
        label: 'دسته بندی',
        href: MOBILE_COURSE_CATEGORIES_HREF,
        icon: 'layout-grid',
        targetBlank: false,
        action: 'course-categories',
    },
    { id: 2, label: 'دوره', href: '/#courses', icon: 'graduation-cap', targetBlank: false },
    { id: 3, label: 'پروفایل', href: '/profile', icon: 'user-round', targetBlank: false },
];

function parseMobileTabHref(href: string): Pick<MobileTabItem, 'href' | 'action'> {
    const normalized = href.trim();
    if (normalized === MOBILE_COURSE_CATEGORIES_HREF || normalized === `/${MOBILE_COURSE_CATEGORIES_HREF}`) {
        return { href: MOBILE_COURSE_CATEGORIES_HREF, action: 'course-categories' };
    }

    return { href: normalized };
}

export function mobileTabsFromSiteMenus(mobileMenus: SiteMenuGroup[] | undefined): MobileTabItem[] {
    const menus = mobileMenus ?? [];
    const tabs: MobileTabItem[] = [];

    for (const menu of menus) {
        const menuHref = menu.url?.trim();
        const label = menu.title?.trim();

        if (menuHref && label) {
            tabs.push({
                id: menu.id,
                label,
                icon: menu.icon,
                targetBlank: false,
                ...parseMobileTabHref(menuHref),
            });
            continue;
        }

        for (const item of menu.items ?? []) {
            const itemLabel = item.title?.trim();
            const itemHref = item.url?.trim();
            if (!itemLabel || !itemHref) {
                continue;
            }

            tabs.push({
                id: item.id,
                label: itemLabel,
                icon: menu.icon,
                targetBlank: item.target_blank,
                ...parseMobileTabHref(itemHref),
            });
        }
    }

    return tabs.length > 0 ? tabs : FALLBACK_TABS;
}

export function collectCourseCategorySlugs(nodes: CourseCategoryNavNode[]): Set<string> {
    const slugs = new Set<string>();

    const walk = (list: CourseCategoryNavNode[]) => {
        for (const node of list) {
            slugs.add(node.slug);
            if (node.children.length > 0) {
                walk(node.children);
            }
        }
    };

    walk(nodes);

    return slugs;
}

/** Match current page URL to the active bottom-tab index. */
export function indexOfActiveMobileTab(
    tabs: MobileTabItem[],
    pageUrl: string,
    courseCategorySlugs?: Set<string>,
): number {
    const page = new URL(pageUrl, 'http://localhost');
    let bestIndex = -1;
    let bestScore = -1;

    tabs.forEach((tab, index) => {
        const score = scoreMobileTabMatch(tab, page, courseCategorySlugs);
        if (score > bestScore) {
            bestScore = score;
            bestIndex = index;
        }
    });

    return bestScore >= 0 ? bestIndex : 0;
}

function scoreMobileTabMatch(tab: MobileTabItem, page: URL, courseCategorySlugs?: Set<string>): number {
    if (tab.action === 'course-categories') {
        const match = page.pathname.match(/^\/categories\/([^/]+)/);
        if (match && courseCategorySlugs?.has(match[1])) {
            return 90 + match[1].length;
        }

        return -1;
    }

    const tabHref = tab.href;
    const tabUrl = new URL(tabHref, 'http://localhost');

    if (tabUrl.hash) {
        if (page.pathname === tabUrl.pathname && page.hash === tabUrl.hash) {
            return 100 + tabUrl.pathname.length;
        }

        return -1;
    }

    if (tabUrl.pathname === '/') {
        if (page.pathname === '/' && !page.hash) {
            return 50;
        }

        return -1;
    }

    if (page.pathname === tabUrl.pathname) {
        return 80 + tabUrl.pathname.length;
    }

    if (page.pathname.startsWith(`${tabUrl.pathname}/`)) {
        return 60 + tabUrl.pathname.length;
    }

    return -1;
}
