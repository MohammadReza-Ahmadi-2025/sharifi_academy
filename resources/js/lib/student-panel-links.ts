import type { LucideIcon } from 'lucide-react';
import {
    Award,
    BookOpen,
    CalendarDays,
    ClipboardCheck,
    FileText,
    LayoutDashboard,
    ListChecks,
    Newspaper,
    UserRound,
} from 'lucide-react';

export type StudentPanelLink = {
    href: string;
    label: string;
    icon: LucideIcon;
    hint?: string;
};

export const studentPanelLinks: StudentPanelLink[] = [
    {
        href: '/student/dashboard',
        label: 'داشبورد',
        icon: LayoutDashboard,
        hint: 'نمای کلی یادگیری',
    },
    {
        href: '/student/courses',
        label: 'دوره‌های من',
        icon: BookOpen,
        hint: 'ثبت‌نام‌ها و کلاس‌ها',
    },
    {
        href: '/student/sessions',
        label: 'ورود به کلاس',
        icon: CalendarDays,
        hint: 'جلسات و Adobe',
    },
    {
        href: '/student/attendance',
        label: 'حضور و غیاب',
        icon: ClipboardCheck,
        hint: 'وضعیت حضور شما',
    },
    {
        href: '/student/quizzes',
        label: 'آزمون‌ها',
        icon: ListChecks,
        hint: 'کوییز دوره‌ها',
    },
    {
        href: '/student/certificates',
        label: 'گواهینامه‌ها',
        icon: Award,
        hint: 'مدارک پایان دوره',
    },
    {
        href: '/student/resume',
        label: 'رزومه من',
        icon: FileText,
        hint: 'سوابق و ارسال برای تایید',
    },
    {
        href: '/account/posts',
        label: 'پست‌های من',
        icon: Newspaper,
        hint: 'بعد از تکمیل پروفایل',
    },
    {
        href: '/profile',
        label: 'پروفایل',
        icon: UserRound,
        hint: 'اطلاعات حساب',
    },
];
