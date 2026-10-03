import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { cn } from '@/lib/utils';
import { Search } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

type Props = {
    search: string;
    onSearchChange: (value: string) => void;
    onSubmit: (event: FormEvent) => void;
    searchPlaceholder?: string;
    onClear?: () => void;
    leading?: ReactNode;
    children?: ReactNode;
    className?: string;
    showClear?: boolean;
};

export function AdminListFilters({
    search,
    onSearchChange,
    onSubmit,
    searchPlaceholder = 'جستجو…',
    onClear,
    leading,
    children,
    className,
    showClear = true,
}: Props) {
    return (
        <Card className={cn('mt-6 space-y-4 p-4', className)}>
            {leading}
            <form onSubmit={onSubmit} className="grid gap-3 lg:grid-cols-12">
                <div className="relative lg:col-span-4">
                    <Search className="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                    <AdminInput
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder={searchPlaceholder}
                        className="pr-9"
                    />
                </div>
                {children}
                <div className="flex flex-wrap items-center gap-2 lg:col-span-12">
                    <AdminButton type="submit">اعمال فیلتر</AdminButton>
                    {showClear && onClear ? (
                        <AdminButton type="button" variant="outline" onClick={onClear}>
                            پاک کردن
                        </AdminButton>
                    ) : null}
                </div>
            </form>
        </Card>
    );
}
