import { ChartCard } from '@/Components/Admin/dashboard/charts/chart-card';
import { ChartTooltip } from '@/Components/Admin/dashboard/charts/chart-tooltip';
import { JalaliDateTime } from '@/Components/Admin/shared/lib/jalali-calendar';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

export type UsersMonthlyPoint = {
    month: string;
    count: number;
};

function formatCount(value: number) {
    return new Intl.NumberFormat('fa-IR').format(value);
}

type UsersGrowthChartProps = {
    data: UsersMonthlyPoint[];
};

export function UsersGrowthChart({ data }: UsersGrowthChartProps) {
    const chartData = data.map((item) => {
        const jalali = JalaliDateTime.fromIsoDate(item.month);

        return {
            label: jalali ? jalali.getMonthName() : item.month,
            count: item.count,
        };
    });

    const total = chartData.reduce((sum, item) => sum + item.count, 0);

    return (
        <ChartCard
            title="روند ثبت‌نام کاربران"
            description="۶ ماه اخیر (تقویم شمسی)"
            footer={
                <div>
                    <div className="text-xs text-slate-500 dark:text-slate-400">جمع ثبت‌نام در بازه</div>
                    <div className="mt-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        {formatCount(total)} نفر
                    </div>
                </div>
            }
        >
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={chartData}>
                        <defs>
                            <linearGradient id="usersFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#00489e" stopOpacity={0.28} />
                                <stop offset="95%" stopColor="#00489e" stopOpacity={0.04} />
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" stroke="#cbd5e1" opacity={0.25} />
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: '#64748b', fontSize: 12 }} />
                        <YAxis hide />
                        <Tooltip content={<ChartTooltip formatter={formatCount} />} />
                        <Area
                            type="monotone"
                            dataKey="count"
                            name="کاربر جدید"
                            stroke="#00489e"
                            strokeWidth={2.5}
                            fill="url(#usersFill)"
                        />
                    </AreaChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
