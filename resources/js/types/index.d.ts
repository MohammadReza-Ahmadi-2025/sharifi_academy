export type AuthUserAvatar = {
    url: string;
    alt?: string | null;
    title?: string | null;
};

export interface User {
    id: number;
    name: string;
    family?: string | null;
    email: string;
    mobile?: string;
    avatar?: AuthUserAvatar | null;
    roles: string[];
    permissions: string[];
}

export type SiteAccountMenu = {
    display_name: string;
    role: string;
    role_label: string;
    profile_url: string;
    panel: { label: string; url: string } | null;
};

export type UserNotificationItem = {
    id: string;
    title: string;
    message: string;
    created_at: string | null;
    read: boolean;
    url: string | null;
};

export type UserNotificationsPayload = {
    unread_count: number;
    items: UserNotificationItem[];
};

export type NavMegaLink = {
    label: string;
    url: string;
    description?: string | null;
};

export type NavMegaColumn = {
    title: string;
    links: NavMegaLink[];
};

export type NavMegaMenu = {
    columns: NavMegaColumn[];
    view_all?: { label: string; url: string };
    featured?: {
        title: string;
        description?: string | null;
        url: string;
        image?: string | null;
    };
};

export type NavItem = {
    id: string;
    label: string;
    url: string;
    type: 'link' | 'mega';
    mega?: NavMegaMenu;
};

export type AdminPanelNavItem = {
    id: string;
    label: string;
    href: string;
    icon?: string;
};

export type CourseCategoryNavNode = {
    id: number;
    title: string;
    slug: string;
    url: string;
    children: CourseCategoryNavNode[];
};

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User | null;
        account: SiteAccountMenu | null;
        notifications: UserNotificationsPayload | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
    app: {
        name: string;
        debug?: boolean;
    };
    navigation: {
        items: NavItem[];
        cta: { label: string; url: string } | null;
    };
    adminPanel: {
        navigation: AdminPanelNavItem[];
    } | null;
    siteSettings: import('@/lib/site-settings').SiteSettings;
    siteMenus: Record<
        string,
        Array<{
            id: number;
            title: string;
            url?: string | null;
            icon?: string | null;
            items: Array<{ id: number; title: string; url: string; target_blank: boolean }>;
        }>
    >;
    siteSocials: Array<{
        id: number;
        type: string;
        url: string;
        label: string;
        target_blank: boolean;
    }>;
    courseCategoryNav: CourseCategoryNavNode[];
    url: string;
};
