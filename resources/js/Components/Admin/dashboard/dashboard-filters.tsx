import { formatAdminDateDisplay } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { useState } from 'react';

export function DashboardFilters() {
    const [from, setFrom] = useState('');
    const [to, setTo] = useState('');

    return (
        <Card className="p-5">
            <div className="mb-4">
                <h3 className="text-sm font-semibold text-slate-900 dark:text-slate-100">فیلتر بازه زمانی</h3>
     
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <PersianDatePicker label="از تاریخ" value={from} onChange={setFrom} placeholder="شروع بازه" />
                <PersianDatePicker label="تا تاریخ" value={to} onChange={setTo} min={from || undefined} placeholder="پایان بازه" />
            </div>

            {(from || to) && (
                <p className="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    بازه انتخابی: {formatAdminDateDisplay(from, 'short')} تا {formatAdminDateDisplay(to, 'short')}
                </p>
            )}
        </Card>
    );
}
