import type { LucideIcon } from 'lucide-react';
import { BookOpen, ClipboardCheck, LayoutDashboard, ScrollText } from 'lucide-react';

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
        label: 'دوره‌ها',
        icon: BookOpen,
        hint: 'محتوا و کلاس‌ها',
    },
    {
        href: '/instructor/resume',
        label: 'رزومه',
        icon: ScrollText,
        hint: 'پروفایل حرفه‌ای',
    },
    {
        href: '/instructor/session-attendances',
        label: 'حضور و غیاب',
        icon: ClipboardCheck,
        hint: 'وضعیت کلاس‌ها',
    },
];
