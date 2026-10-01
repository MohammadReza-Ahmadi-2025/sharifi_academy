import { useAdminTheme } from '@/Components/Admin/shared/contexts/admin-theme-context';
import { cn } from '@/lib/utils';
import { Moon, Sun } from 'lucide-react';

export function ThemeToggle({ className }: { className?: string }) {
    const { isDark, toggleTheme, mounted } = useAdminTheme();

    if (!mounted) {
        return (
            <button
                type="button"
                aria-label="تغییر حالت نمایش"
                className={cn(
                    'inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600',
                    'dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300',
                    className,
                )}
            />
        );
    }

    return (
        <button
            type="button"
            onClick={toggleTheme}
            aria-label={isDark ? 'فعال‌سازی حالت روشن' : 'فعال‌سازی حالت تیره'}
            title={isDark ? 'حالت روشن' : 'حالت تیره'}
            className={cn(
                'inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition-colors',
                'hover:bg-slate-50 hover:text-slate-900',
                'dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 dark:focus-visible:ring-slate-600 dark:focus-visible:ring-offset-slate-950',
                className,
            )}
        >
            {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
        </button>
    );
}
