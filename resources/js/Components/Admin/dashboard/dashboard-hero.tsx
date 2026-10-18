import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { LayoutDashboard } from 'lucide-react';

export function DashboardHero() {
    return (
        <div
            className="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-l from-primary/[0.08] via-white to-white px-5 py-6 shadow-sm dark:border-slate-800 dark:from-primary/15 dark:via-slate-900 dark:to-slate-900 md:px-8 md:py-7"
        >
            <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-start gap-4">
                    <div
                        className="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary sm:flex"
                        aria-hidden
                    >
                        <LayoutDashboard className="size-6" />
                    </div>
                    <div className="space-y-1.5">
                        <h1 className="text-lg font-bold text-slate-900 dark:text-slate-50 md:text-xl">به پنل مدیریت خوش آمدید</h1>
                        <p className="max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            نمای کلی آکادمی — آمار لحظه‌ای و نمودارها در یک نگاه.
                        </p>
                    </div>
                </div>
                <AdminButton href="/" variant="outline" className="shrink-0 border-slate-200 bg-white/80 dark:border-slate-700 dark:bg-slate-900/80">
                    مشاهده سایت
                </AdminButton>
            </div>
        </div>
    );
}
