import { PersianCalendarDialog } from '@/Components/Admin/shared/layout/persian-calendar-dialog';
import {
    formatPersianDateTime,
    persianDateTimeClassName,
} from '@/Components/Admin/shared/lib/format-persian-datetime';
import { CalendarDays, Clock3 } from 'lucide-react';
import { useEffect, useState } from 'react';

export function HeaderDateTime() {
    const [now, setNow] = useState<Date | null>(null);
    const [isCalendarOpen, setIsCalendarOpen] = useState(false);

    useEffect(() => {
        setNow(new Date());

        const intervalId = window.setInterval(() => {
            setNow(new Date());
        }, 1000);

        return () => window.clearInterval(intervalId);
    }, []);

    if (!now) {
        return <div className="hidden h-10 w-44 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-900 lg:block" />;
    }

    return (
        <>
            <button
                type="button"
                onClick={() => setIsCalendarOpen(true)}
                title="مشاهده تقویم و تعطیلات"
                aria-label="مشاهده تقویم و تعطیلات"
                className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-teal-300 hover:bg-teal-50/60 hover:text-teal-800 lg:hidden dark:border-slate-700 dark:text-slate-300 dark:hover:border-teal-500/40 dark:hover:bg-teal-500/10"
            >
                <CalendarDays className="h-4 w-4" />
            </button>

            <button
                type="button"
                onClick={() => setIsCalendarOpen(true)}
                title="مشاهده تقویم و تعطیلات"
                aria-label="مشاهده تقویم و تعطیلات"
                className="hidden items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 transition hover:border-teal-300 hover:bg-teal-50/60 lg:flex dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-teal-500/40 dark:hover:bg-teal-500/10"
            >
                <div className="flex items-center gap-2 border-l border-slate-200 pl-3 dark:border-slate-700">
                    <CalendarDays className="h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500" />
                    <span className={`whitespace-nowrap text-xs ${persianDateTimeClassName}`}>
                        {formatPersianDateTime(now, 'long')}
                    </span>
                </div>

                <div className="flex items-center gap-2">
                    <Clock3 className="h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500" />
                    <span className={`text-xs ${persianDateTimeClassName}`}>{formatPersianDateTime(now, 'time')}</span>
                </div>
            </button>

            <PersianCalendarDialog isOpen={isCalendarOpen} onClose={() => setIsCalendarOpen(false)} />
        </>
    );
}
