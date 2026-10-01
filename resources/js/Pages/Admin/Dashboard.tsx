import { DashboardChartsGrid, type DashboardCharts } from '@/Components/Admin/dashboard/dashboard-charts-grid';
import { DashboardFilters } from '@/Components/Admin/dashboard/dashboard-filters';
import { DashboardPageHeader } from '@/Components/Admin/dashboard/dashboard-page-header';
import { QuickActions } from '@/Components/Admin/dashboard/quick-actions';
import { StatsGrid, type DashboardStat } from '@/Components/Admin/dashboard/stats-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

type Props = {
    stats: DashboardStat[];
    charts: DashboardCharts;
};

export default function Dashboard({ stats, charts }: Props) {
    return (
        <AdminLayout>
            <Head title="داشبورد ادمین" />

            <div className="space-y-6">
                <DashboardPageHeader />

                <DashboardFilters />

                <StatsGrid stats={stats} />

                <DashboardChartsGrid charts={charts} />

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <CardWelcome className="xl:col-span-2" />
                    <div className="xl:col-span-1">
                        <QuickActions />
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function CardWelcome({ className }: { className?: string }) {
    return (
        <div
            className={`kpi-glow rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900 ${className ?? ''}`}
        >
            <h2 className="text-lg font-semibold text-slate-900 dark:text-slate-100">به پنل مدیریت خوش آمدید</h2>
            <p className="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                کامپوننت‌های shared Nexin (DatePicker، Table، Select، نمودار Recharts) در این پروژه در مسیر
                <code className="mx-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800">Components/Admin/shared</code>
                آماده استفاده در ماژول‌های بعدی هستند.
            </p>
        </div>
    );
}
