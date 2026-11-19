import {
    BookOpen,
    GraduationCap,
    Home,
    LayoutGrid,
    LucideIcon,
    Newspaper,
    Plus,
    ShoppingBag,
    UserRound,
} from 'lucide-react';
import type { ComponentType } from 'react';

const TAB_ICON_CLASS = 'size-6 shrink-0 stroke-[1.75]';

function TabIcon({ icon: Icon, className }: { icon: LucideIcon; className?: string }) {
    return <Icon className={className ?? TAB_ICON_CLASS} stroke="currentColor" aria-hidden />;
}

export function TabBarIconHome() {
    return <TabIcon icon={Home} />;
}

export function TabBarIconCategory() {
    return <TabIcon icon={LayoutGrid} />;
}

export function TabBarIconCreatePost() {
    return <TabIcon icon={Plus} className="size-7 shrink-0 stroke-[2.25]" />;
}

export function TabBarIconPosts() {
    return <TabIcon icon={Newspaper} />;
}

export function TabBarIconProfile() {
    return <TabIcon icon={UserRound} />;
}

/** Fallback icons by tab position when منو آیکن ندارد */
export const DEFAULT_MOBILE_TAB_ICONS = [
    TabBarIconHome,
    TabBarIconCategory,
    TabBarIconCreatePost,
    TabBarIconPosts,
    TabBarIconProfile,
] as const;

const MOBILE_TAB_ICON_MAP: Record<string, LucideIcon> = {
    home: Home,
    'layout-grid': LayoutGrid,
    grid: LayoutGrid,
    category: LayoutGrid,
    categories: LayoutGrid,
    plus: Plus,
    add: Plus,
    'plus-circle': Plus,
    newspaper: Newspaper,
    posts: Newspaper,
    blog: Newspaper,
    'graduation-cap': GraduationCap,
    course: GraduationCap,
    courses: GraduationCap,
    'user-round': UserRound,
    user: UserRound,
    profile: UserRound,
    book: BookOpen,
    books: BookOpen,
    shop: ShoppingBag,
};

export function resolveMobileTabBarIcon(
    iconName: string | null | undefined,
    index: number,
): ComponentType {
    const fallback = DEFAULT_MOBILE_TAB_ICONS[index % DEFAULT_MOBILE_TAB_ICONS.length];
    const key = iconName?.trim().toLowerCase();
    if (!key) {
        return fallback;
    }

    const Icon = MOBILE_TAB_ICON_MAP[key];
    if (!Icon) {
        return fallback;
    }

    const emphasized = key === 'plus' || key === 'add' || key === 'plus-circle';

    return function MobileTabBarLucideIcon() {
        return (
            <TabIcon
                icon={Icon}
                className={emphasized ? 'size-7 shrink-0 stroke-[2.25]' : TAB_ICON_CLASS}
            />
        );
    };
}
