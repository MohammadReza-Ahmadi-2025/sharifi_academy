import type { ModerationQueue } from '@/Components/Admin/dashboard/dashboard-moderation-alerts';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowLeft, BarChart3, ExternalLink, GraduationCap, UserCheck } from 'lucide-react';

type QuickAction = {
    label: string;
    description: string;
    href: string;
    icon: typeof ExternalLink;
    featured?: boolean;
    badge?: number;
};

const baseActions: QuickAction[] = [
    {
        label: 'صفحه اصلی سایت',
        description: 'باز کردن وب‌سایت آکادمی',
        href: '/',
        icon: ExternalLink,
        featured: true,
    },
    {
        label: 'دوره‌ها',
        description: 'مدیریت دوره و کلاس',
        href: '/admin/courses',
        icon: GraduationCap,
    },
    {
        label: 'آمار بازدید',
        description: 'گزارش بازدید صفحات',
        href: '/admin/visits',
        icon: BarChart3,
    },
];

type Props = {
    moderation?: ModerationQueue | null;
};

export function QuickActions({ moderation }: Props) {
    const actions: QuickAction[] = [...baseActions];
    if (moderation && moderation.total > 0) {
        actions.unshift({
            label: 'تایید اساتید',
            description: `${moderation.total.toLocaleString('fa-IR')} مورد در صف بررسی`,
            href: '/admin/instructor-moderation',
            icon: UserCheck,
            featured: true,
            badge: moderation.total,
        });
    }

    return (
        <Card>
            <div className="mb-4">
                <h3 className="text-sm font-semibold text-slate-900 dark:text-slate-100">دسترسی سریع</h3>
                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">میانبرهای پرکاربرد برای عملیات روزانه</p>
            </div>

            <div className="space-y-3">
                {actions.map((action) => {
                    const Icon = action.icon;
                    const badge = action.badge ?? 0;
                    const className = cn(
                        'flex w-full items-center justify-between rounded-lg border px-4 py-3 text-right transition-colors',
                        action.featured
                            ? 'border-sky-200 bg-sky-50 hover:bg-sky-100 dark:border-sky-500/20 dark:bg-sky-500/10 dark:hover:bg-sky-500/15'
                            : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800',
                    );

                    return (
                        <Link key={action.href} href={action.href} className={className}>
                            <div className="flex min-w-0 items-center gap-3">
                                <div
                                    className={cn(
                                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
                                        action.featured
                                            ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300'
                                            : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300',
                                    )}
                                >
                                    <Icon className="h-4 w-4" />
                                </div>

                                <div className="min-w-0">
                                    <p className="flex items-center gap-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                                        {action.label}
                                        {badge > 0 ? (
                                            <Badge variant="warning" className="text-[10px]">
                                                {badge.toLocaleString('fa-IR')}
                                            </Badge>
                                        ) : null}
                                    </p>
                                    <p className="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">{action.description}</p>
                                </div>
                            </div>

                            <ArrowLeft className="h-4 w-4 shrink-0 text-slate-400" />
                        </Link>
                    );
                })}
            </div>
        </Card>
    );
}
