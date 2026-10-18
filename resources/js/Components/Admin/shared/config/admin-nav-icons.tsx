import type { AdminPanelNavItem } from '@/types';
import {
    CircleDot,
    ClipboardList,
    FolderTree,
    GraduationCap,
    History,
    Home,
    LayoutDashboard,
    MapPin,
    MessageSquare,
    Newspaper,
    Package,
    Settings,
    Share2,
    ShieldCheck,
    Sparkles,
    Tag,
    UserCheck,
    UserPlus,
    Users,
    FileStack,
    FileText,
    Warehouse,
    Menu,
    Eye,
    type LucideIcon,
} from 'lucide-react';

const iconByKey: Record<string, LucideIcon> = {
    dashboard: LayoutDashboard,
    users: Users,
    roles: ShieldCheck,
    courses: GraduationCap,
    'instructor-moderation': UserCheck,
    enrollments: UserPlus,
    categories: FolderTree,
    'catalog-products': Package,
    'catalog-brands': Tag,
    'catalog-warehouses': Warehouse,
    'catalog-inventory': ClipboardList,
    skills: Sparkles,
    locations: MapPin,
    files: FileStack,
    'session-attendances': UserCheck,
    socials: Share2,
    comments: MessageSquare,
    'blog-posts': Newspaper,
    pages: FileText,
    stories: CircleDot,
    'home-sections': Home,
    'home-banners': Home,
    menus: Menu,
    visits: Eye,
    'site-settings': Settings,
    'activity-logs': History,
};

export function getAdminNavIcon(item: AdminPanelNavItem): LucideIcon {
    const key = item.icon ?? item.id;

    return iconByKey[key] ?? LayoutDashboard;
}
