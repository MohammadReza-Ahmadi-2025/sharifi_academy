import { RolesDistributionChart, type RoleSlice } from '@/Components/Admin/dashboard/charts/roles-distribution-chart';
import { UsersGrowthChart, type UsersMonthlyPoint } from '@/Components/Admin/dashboard/charts/users-growth-chart';

export type DashboardCharts = {
    usersMonthly: UsersMonthlyPoint[];
    rolesBreakdown: RoleSlice[];
};

type DashboardChartsGridProps = {
    charts: DashboardCharts;
};

export function DashboardChartsGrid({ charts }: DashboardChartsGridProps) {
    const hasMonthly = charts.usersMonthly.length > 0;
    const hasRoles = charts.rolesBreakdown.some((slice) => slice.value > 0);

    if (!hasMonthly && !hasRoles) {
        return null;
    }

    return (
        <section className="grid gap-6 xl:grid-cols-2">
            {hasMonthly ? <UsersGrowthChart data={charts.usersMonthly} /> : null}
            {hasRoles ? <RolesDistributionChart slices={charts.rolesBreakdown} /> : null}
        </section>
    );
}
