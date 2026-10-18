import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { AlertCircle, GraduationCap, UserCheck } from 'lucide-react';

export type ModerationQueue = {
    courses_pending: number;
    resumes_pending: number;
    total: number;
};

type Props = {
    moderation: ModerationQueue;
};

export function DashboardModerationAlerts({ moderation }: Props) {
    if (moderation.total <= 0) {
        return null;
    }

    return (
        <Card className="border-amber-200 bg-amber-50/80 dark:border-amber-500/30 dark:bg-amber-500/10">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div className="flex gap-3">
                    <div
                        className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200"
                        aria-hidden
                    >
                        <AlertCircle className="size-5" />
                    </div>
                    <div>
                        <h2 className="text-sm font-semibold text-amber-950 dark:text-amber-100">
                            {moderation.total.toLocaleString('fa-IR')} مورد در صف تایید اساتید
                        </h2>
                        <ul className="mt-2 space-y-1 text-sm text-amber-900/90 dark:text-amber-100/90">
                            {moderation.courses_pending > 0 ? (
                                <li className="flex items-center gap-2">
                                    <GraduationCap className="size-4 shrink-0 opacity-70" aria-hidden />
                                    {moderation.courses_pending.toLocaleString('fa-IR')} دوره در انتظار انتشار
                                </li>
                            ) : null}
                            {moderation.resumes_pending > 0 ? (
                                <li className="flex items-center gap-2">
                                    <UserCheck className="size-4 shrink-0 opacity-70" aria-hidden />
                                    {moderation.resumes_pending.toLocaleString('fa-IR')} رزومه در انتظار بررسی
                                </li>
                            ) : null}
                        </ul>
                    </div>
                </div>
                <div className="flex flex-wrap gap-2 sm:shrink-0">
                    {moderation.courses_pending > 0 ? (
                        <AdminButton href="/admin/courses?status=pending_review" variant="outline" size="sm">
                            دوره‌های در انتظار
                        </AdminButton>
                    ) : null}
                    <AdminButton href="/admin/instructor-moderation" size="sm">
                        صف تایید اساتید
                    </AdminButton>
                </div>
            </div>
        </Card>
    );
}
