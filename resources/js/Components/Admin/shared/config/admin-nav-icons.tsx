import type { AdminPanelNavItem } from '@/types';
import { BookOpen, GraduationCap, LayoutDashboard, ShieldCheck, ShoppingCart, Users, type LucideIcon } from 'lucide-react';

const iconById: Record<string, LucideIcon> = {
    dashboard: LayoutDashboard,
    users: Users,
    roles: ShieldCheck,
    courses: GraduationCap,
    books: BookOpen,
    orders: ShoppingCart,
};

export function getAdminNavIcon(item: AdminPanelNavItem): LucideIcon {
    return iconById[item.id] ?? LayoutDashboard;
}
