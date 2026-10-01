import { ChartCard } from '@/Components/Admin/dashboard/charts/chart-card';
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';

export type RoleSlice = {
    name: string;
    value: number;
    color: string;
};

type RolesDistributionChartProps = {
    slices: RoleSlice[];
};

function formatCount(value: number) {
    return new Intl.NumberFormat('fa-IR').format(value);
}

export function RolesDistributionChart({ slices }: RolesDistributionChartProps) {
    const data = slices.filter((slice) => slice.value > 0);
    const total = data.reduce((sum, slice) => sum + slice.value, 0);

    if (total <= 0) {
        return (
            <ChartCard title="توزیع نقش‌ها" description="سهم هر نقش در کاربران">
                <div className="flex h-64 items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                    داده‌ای برای نمایش نیست
                </div>
            </ChartCard>
        );
    }

    return (
        <ChartCard
            title="توزیع نقش‌ها"
            description="سهم هر نقش در کاربران"
            footer={
                <div className="space-y-2">
                    {data.map((slice) => (
                        <div key={slice.name} className="flex items-center justify-between gap-3 text-sm">
                            <div className="flex items-center gap-2">
                                <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: slice.color }} />
                                <span className="text-slate-600 dark:text-slate-300">{slice.name}</span>
                            </div>
                            <span className="font-medium text-slate-900 dark:text-slate-100">{formatCount(slice.value)}</span>
                        </div>
                    ))}
                </div>
            }
        >
            <div className="h-64">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={data}
                            dataKey="value"
                            nameKey="name"
                            innerRadius={58}
                            outerRadius={88}
                            paddingAngle={3}
                            strokeWidth={0}
                        >
                            {data.map((slice) => (
                                <Cell key={slice.name} fill={slice.color} />
                            ))}
                        </Pie>
                        <Tooltip
                            formatter={(value) => formatCount(typeof value === 'number' ? value : Number(value))}
                        />
                    </PieChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
