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
};

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
    };
    app: {
        name: string;
    };
    navigation: {
        items: NavItem[];
        cta: { label: string; url: string } | null;
    };
    adminPanel: {
        navigation: AdminPanelNavItem[];
    } | null;
    url: string;
};
