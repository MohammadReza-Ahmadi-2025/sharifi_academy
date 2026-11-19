import type { CourseCategoryNavNode, PageProps } from '@/types';

export const MOBILE_COURSE_CATEGORIES_HREF = '#course-categories';
export const MOBILE_CREATE_POST_HREF = '#create-post';

export type MobileTabAction = 'course-categories' | 'create-post';

export type MobileTabItem = {
    id: number;
    label: string;
    href: string;
    icon?: string | null;
    targetBlank: boolean;
    action?: MobileTabAction;
    emphasized?: boolean;
};

type SiteMenuGroup = PageProps['siteMenus'][string][number];

type MobileTabBarOptions = {
    canCreatePost: boolean;
    createPostUrl: string | null;
};

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
    {
        id: 2,
        label: 'افزودن',
        href: MOBILE_CREATE_POST_HREF,
        icon: 'plus',
        targetBlank: false,
        action: 'create-post',
        emphasized: true,
    },
    { id: 3, label: 'پست‌ها', href: '/posts', icon: 'newspaper', targetBlank: false },
    { id: 4, label: 'پروفایل', href: '/profile', icon: 'user-round', targetBlank: false },
];

function parseMobileTabHref(href: string): Pick<MobileTabItem, 'href' | 'action' | 'emphasized'> {
    const normalized = href.trim();

    if (normalized === MOBILE_COURSE_CATEGORIES_HREF || normalized === `/${MOBILE_COURSE_CATEGORIES_HREF}`) {
        return { href: MOBILE_COURSE_CATEGORIES_HREF, action: 'course-categories' };
    }

    if (
        normalized === MOBILE_CREATE_POST_HREF
        || normalized === `/${MOBILE_CREATE_POST_HREF}`
        || normalized === '/#courses'
        || normalized.endsWith('/posts/create')
    ) {
        return { href: MOBILE_CREATE_POST_HREF, action: 'create-post', emphasized: true };
    }

    return { href: normalized };
}

function tabsFromMenus(mobileMenus: SiteMenuGroup[] | undefined): MobileTabItem[] {
    const menus = mobileMenus ?? [];
    const tabs: MobileTabItem[] = [];

    for (const menu of menus) {
        const menuHref = menu.url?.trim();
        const label = menu.title?.trim();

        if (menuHref && label) {
            const parsed = parseMobileTabHref(menuHref);
            tabs.push({
                id: menu.id,
                label: parsed.action === 'create-post' ? 'افزودن' : label,
                icon: parsed.action === 'create-post' ? 'plus' : menu.icon,
                targetBlank: false,
                ...parsed,
            });
            continue;
        }

        for (const item of menu.items ?? []) {
            const itemLabel = item.title?.trim();
            const itemHref = item.url?.trim();
            if (!itemLabel || !itemHref) {
                continue;
            }

            const parsed = parseMobileTabHref(itemHref);
            tabs.push({
                id: item.id,
                label: parsed.action === 'create-post' ? 'افزودن' : itemLabel,
                icon: parsed.action === 'create-post' ? 'plus' : menu.icon,
                targetBlank: item.target_blank,
                ...parsed,
            });
        }
    }

    return tabs;
}

function tabPathname(href: string): string {
    try {
        return new URL(href, 'http://localhost').pathname;
    } catch {
        return href;
    }
}

function ensurePostsTab(tabs: MobileTabItem[]): MobileTabItem[] {
    // Legacy menu may still point «پست‌ها» at /blog (articles) — remap to community feed.
    const remapped = tabs.map((tab) => {
        const path = tabPathname(tab.href);
        if (path === '/blog' && (tab.label.includes('پست') || tab.icon === 'newspaper')) {
            return { ...tab, href: '/posts', label: 'پست‌ها', icon: 'newspaper' };
        }

        return tab;
    });

    if (remapped.some((tab) => tabPathname(tab.href) === '/posts')) {
        return remapped;
    }

    const profileIndex = remapped.findIndex(
        (tab) => tab.href === '/profile' || tab.icon === 'user-round',
    );
    const postsTab: MobileTabItem = {
        id: -30,
        label: 'پست‌ها',
        href: '/posts',
        icon: 'newspaper',
        targetBlank: false,
    };

    if (profileIndex >= 0) {
        const next = [...remapped];
        next.splice(profileIndex, 0, postsTab);

        return next;
    }

    return [...remapped, postsTab];
}

function resolveCreatePostTab(tabs: MobileTabItem[], options: MobileTabBarOptions): MobileTabItem[] {
    const createIndex = tabs.findIndex((tab) => tab.action === 'create-post');

    if (createIndex < 0) {
        if (!options.canCreatePost && !options.createPostUrl) {
            return tabs;
        }

        const mid = Math.min(2, tabs.length);
        const next = [...tabs];
        next.splice(mid, 0, {
            id: -20,
            label: 'افزودن',
            href: options.createPostUrl || MOBILE_CREATE_POST_HREF,
            icon: 'plus',
            targetBlank: false,
            action: 'create-post',
            emphasized: true,
        });

        return next;
    }

    if (!options.canCreatePost && options.createPostUrl === null) {
        return tabs.filter((tab) => tab.action !== 'create-post');
    }

    return tabs.map((tab) => {
        if (tab.action !== 'create-post') {
            return tab;
        }

        return {
            ...tab,
            href: options.createPostUrl || '/login',
            icon: 'plus',
            emphasized: true,
            label: 'افزودن',
        };
    });
}

export function mobileTabsFromSiteMenus(
    mobileMenus: SiteMenuGroup[] | undefined,
    options: MobileTabBarOptions = { canCreatePost: false, createPostUrl: '/login' },
): MobileTabItem[] {
    const fromMenus = tabsFromMenus(mobileMenus);
    const base = fromMenus.length > 0 ? fromMenus : FALLBACK_TABS;

    return resolveCreatePostTab(ensurePostsTab(base), options);
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

    if (tab.action === 'create-post') {
        if (page.pathname.endsWith('/posts/create')) {
            return 100;
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
