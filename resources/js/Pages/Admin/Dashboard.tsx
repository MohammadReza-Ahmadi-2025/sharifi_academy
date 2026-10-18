import { DashboardChartsGrid, type DashboardCharts } from '@/Components/Admin/dashboard/dashboard-charts-grid';
import { DashboardHero } from '@/Components/Admin/dashboard/dashboard-hero';
import {
    DashboardModerationAlerts,
    type ModerationQueue,
} from '@/Components/Admin/dashboard/dashboard-moderation-alerts';
import { QuickActions } from '@/Components/Admin/dashboard/quick-actions';
import { StatsGrid, type DashboardStatGroup } from '@/Components/Admin/dashboard/stats-grid';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

type Props = {
    statGroups: DashboardStatGroup[];
    charts: DashboardCharts;
    moderation: ModerationQueue | null;
};

export default function Dashboard({ statGroups, charts, moderation }: Props) {
    return (
        <AdminLayout>
            <Head title="داشبورد ادمین" />

            <div className="space-y-6">
                <DashboardHero />

                {moderation ? <DashboardModerationAlerts moderation={moderation} /> : null}

                <div className="grid gap-6 xl:grid-cols-[1fr_minmax(17rem,22rem)] xl:items-start">
                    <div className="min-w-0 space-y-6">
                        <StatsGrid groups={statGroups} />
                        <DashboardChartsGrid charts={charts} />
                    </div>

                    <aside className="xl:sticky xl:top-4">
                        <QuickActions moderation={moderation} />
                    </aside>
                </div>
            </div>
        </AdminLayout>
    );
}
