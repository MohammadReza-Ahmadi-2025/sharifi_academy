import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';

export function DashboardPageHeader() {
    return (
        <Card className="flex flex-col justify-between lg:flex-row lg:items-center">
            <div className="space-y-1">
                <h2 className="text-lg font-semibold text-slate-900 dark:text-slate-100">نمای کلی داشبورد</h2>
                <p className="text-sm text-slate-500 dark:text-slate-400">
                    خلاصه کاربران و نقش‌های آکادمی شریفی — همان الگوی پنل Nexin.
                </p>
            </div>

            <div className="mt-4 flex items-center gap-3 lg:mt-0">
                <AdminButton href="/" variant="outline">
                    مشاهده سایت
                </AdminButton>
            </div>
        </Card>
    );
}
