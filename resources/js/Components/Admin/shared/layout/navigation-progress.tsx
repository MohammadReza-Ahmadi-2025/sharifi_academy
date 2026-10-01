import { cn } from '@/lib/utils';
import { router, usePage } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

const SAFETY_TIMEOUT_MS = 12_000;

export function NavigationProgress() {
    const { url } = usePage().props as { url: string };
    const [isNavigating, setIsNavigating] = useState(false);
    const timeoutRef = useRef<number | null>(null);

    const clearNavigating = () => {
        setIsNavigating(false);
        if (timeoutRef.current != null) {
            window.clearTimeout(timeoutRef.current);
            timeoutRef.current = null;
        }
    };

    const startNavigating = () => {
        setIsNavigating(true);
        if (timeoutRef.current != null) {
            window.clearTimeout(timeoutRef.current);
        }
        timeoutRef.current = window.setTimeout(() => {
            setIsNavigating(false);
            timeoutRef.current = null;
        }, SAFETY_TIMEOUT_MS);
    };

    useEffect(() => {
        clearNavigating();
    }, [url]);

    useEffect(() => {
        const start = () => startNavigating();
        const finish = () => clearNavigating();

        const removeStart = router.on('start', start);
        const removeFinish = router.on('finish', finish);
        const removeError = router.on('error', finish);
        const removeInvalid = router.on('invalid', finish);

        return () => {
            removeStart();
            removeFinish();
            removeError();
            removeInvalid();
            if (timeoutRef.current != null) {
                window.clearTimeout(timeoutRef.current);
            }
        };
    }, []);

    return (
        <>
            <div
                className={cn(
                    'pointer-events-none fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden transition-opacity duration-200',
                    isNavigating ? 'opacity-100' : 'opacity-0',
                )}
                aria-hidden={!isNavigating}
            >
                <div
                    className={cn(
                        'h-full w-1/3 rounded-full bg-slate-900 dark:bg-slate-100',
                        isNavigating && 'admin-nav-progress-bar',
                    )}
                />
            </div>

            {isNavigating && (
                <div
                    className="fixed inset-0 z-[90] flex items-center justify-center bg-slate-50/70 backdrop-blur-[2px] dark:bg-slate-950/70"
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                >
                    <div className="flex flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white px-8 py-6 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <Loader2 className="h-8 w-8 animate-spin text-slate-900 dark:text-slate-100" />
                        <div className="text-center">
                            <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">در حال بارگذاری</p>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">لطفاً چند لحظه صبر کنید...</p>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
