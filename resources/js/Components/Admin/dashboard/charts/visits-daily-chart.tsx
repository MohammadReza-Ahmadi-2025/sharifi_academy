import { ChartCard } from '@/Components/Admin/dashboard/charts/chart-card';
import { ChartTooltip } from '@/Components/Admin/dashboard/charts/chart-tooltip';
import { formatGregorianApiDate } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

export type VisitsDailyPoint = {
    day: string;
    count: number;
};

function formatCount(value: number) {
    return new Intl.NumberFormat('fa-IR').format(value);
}

type VisitsDailyChartProps = {
    data: VisitsDailyPoint[];
};

export function VisitsDailyChart({ data }: VisitsDailyChartProps) {
    const chartData = data.map((item) => ({
        label: formatGregorianApiDate(item.day, 'short'),
        count: item.count,
    }));

    const total = chartData.reduce((sum, item) => sum + item.count, 0);

    return (
        <ChartCard
            title="بازدید روزانه"
            description="۷ روز اخیر"
            footer={
                <div>
                    <div className="text-xs text-slate-500 dark:text-slate-400">جمع بازدید در بازه</div>
                    <div className="mt-1 text-sm font-semibold text-sky-600 dark:text-sky-400">
                        {formatCount(total)} بازدید
                    </div>
                </div>
            }
        >
            <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={chartData}>
                        <CartesianGrid strokeDasharray="3 3" stroke="#cbd5e1" opacity={0.25} />
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: '#64748b', fontSize: 11 }} />
                        <YAxis hide />
                        <Tooltip content={<ChartTooltip formatter={formatCount} />} />
                        <Bar dataKey="count" name="بازدید" fill="#0ea5e9" radius={[6, 6, 0, 0]} maxBarSize={48} />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </ChartCard>
    );
}
