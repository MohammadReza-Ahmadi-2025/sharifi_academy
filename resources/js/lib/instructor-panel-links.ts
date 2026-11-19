import type { LucideIcon } from 'lucide-react';
import { BookOpen, ClipboardCheck, LayoutDashboard, Newspaper, ScrollText } from 'lucide-react';

export type InstructorPanelLink = {
    href: string;
    label: string;
    icon: LucideIcon;
    hint?: string;
};

export const instructorPanelLinks: InstructorPanelLink[] = [
    {
        href: '/instructor',
        label: 'داشبورد',
        icon: LayoutDashboard,
        hint: 'نمای کلی تدریس',
    },
    {
        href: '/instructor/courses',
        label: 'دوره‌های من',
        icon: BookOpen,
        hint: 'محتوا و کلاس‌ها',
    },
    {
        href: '/instructor/session-attendances',
        label: 'ورود به کلاس',
        icon: ClipboardCheck,
        hint: 'جلسات و Adobe',
    },
    {
        href: '/instructor/resume',
        label: 'رزومه',
        icon: ScrollText,
        hint: 'پروفایل حرفه‌ای',
    },
    {
        href: '/account/posts',
        label: 'پست‌های من',
        icon: Newspaper,
        hint: 'بعد از تکمیل پروفایل',
    },
];
