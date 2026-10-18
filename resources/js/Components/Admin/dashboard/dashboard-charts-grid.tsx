import { RolesDistributionChart, type RoleSlice } from '@/Components/Admin/dashboard/charts/roles-distribution-chart';
import { UsersGrowthChart, type UsersMonthlyPoint } from '@/Components/Admin/dashboard/charts/users-growth-chart';
import { VisitsDailyChart, type VisitsDailyPoint } from '@/Components/Admin/dashboard/charts/visits-daily-chart';

export type DashboardCharts = {
    usersMonthly: UsersMonthlyPoint[];
    rolesBreakdown: RoleSlice[];
    visitsDaily: VisitsDailyPoint[];
};

type DashboardChartsGridProps = {
    charts: DashboardCharts;
};

export function DashboardChartsGrid({ charts }: DashboardChartsGridProps) {
    const hasMonthly = charts.usersMonthly.length > 0;
    const hasRoles = charts.rolesBreakdown.some((slice) => slice.value > 0);
    const hasVisits = charts.visitsDaily.length > 0;

    if (!hasMonthly && !hasRoles && !hasVisits) {
        return null;
    }

    return (
        <section className="space-y-4">
            <h2 className="text-sm font-semibold text-slate-700 dark:text-slate-300">نمودارها</h2>
            <div className="space-y-6">
                {hasVisits ? <VisitsDailyChart data={charts.visitsDaily} /> : null}
                <div className="grid gap-6 lg:grid-cols-2">
                    {hasMonthly ? <UsersGrowthChart data={charts.usersMonthly} /> : null}
                    {hasRoles ? <RolesDistributionChart slices={charts.rolesBreakdown} /> : null}
                </div>
            </div>
        </section>
    );
}
