import { useAdminTheme } from '@/Components/Admin/shared/contexts/admin-theme-context';
import { cn } from '@/lib/utils';
import { Moon, Sun } from 'lucide-react';

export function ProfileThemeMenuItem() {
    const { isDark, toggleTheme, mounted } = useAdminTheme();

    return (
        <button
            type="button"
            onClick={toggleTheme}
            disabled={!mounted}
            className={cn(
                'flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition-colors',
                'hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800',
            )}
        >
            <span className="flex items-center gap-3">
                {isDark ? <Sun className="h-4 w-4 text-slate-400" /> : <Moon className="h-4 w-4 text-slate-400" />}
                {isDark ? 'حالت روشن' : 'حالت تیره'}
            </span>
            <span className="text-[10px] text-slate-400">{isDark ? 'فعال: تیره' : 'فعال: روشن'}</span>
        </button>
    );
}
