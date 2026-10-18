import {
    BookOpen,
    GraduationCap,
    Home,
    LayoutGrid,
    LucideIcon,
    ShoppingBag,
    UserRound,
} from 'lucide-react';
import type { ComponentType } from 'react';

const TAB_ICON_CLASS = 'size-[2.35em] shrink-0 stroke-[1.75]';

function TabIcon({ icon: Icon }: { icon: LucideIcon }) {
    return <Icon className={TAB_ICON_CLASS} stroke="currentColor" aria-hidden />;
}

export function TabBarIconHome() {
    return <TabIcon icon={Home} />;
}

export function TabBarIconCategory() {
    return <TabIcon icon={LayoutGrid} />;
}

export function TabBarIconCourse() {
    return <TabIcon icon={GraduationCap} />;
}

export function TabBarIconProfile() {
    return <TabIcon icon={UserRound} />;
}

/** Fallback icons by tab position when منو آیکن ندارد */
export const DEFAULT_MOBILE_TAB_ICONS = [
    TabBarIconHome,
    TabBarIconCategory,
    TabBarIconCourse,
    TabBarIconProfile,
] as const;

const MOBILE_TAB_ICON_MAP: Record<string, LucideIcon> = {
    home: Home,
    'layout-grid': LayoutGrid,
    grid: LayoutGrid,
    category: LayoutGrid,
    categories: LayoutGrid,
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

    return function MobileTabBarLucideIcon() {
        return <TabIcon icon={Icon} />;
    };
}
