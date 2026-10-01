import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { DEFAULT_PAGE_SIZE, PAGE_SIZE_OPTIONS } from '@/Components/Admin/shared/config/pagination';
import { cn } from '@/lib/utils';

export type SimpleTablePaginationProps = {
    currentPage: number;
    lastPage: number;
    total: number;
    perPage: number;
    from: number | null;
    to: number | null;
    disabled?: boolean;
    itemLabel?: string;
    className?: string;
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
};

export function SimpleTablePagination({
    currentPage,
    lastPage,
    total,
    perPage,
    from,
    to,
    disabled = false,
    itemLabel = 'مورد',
    className,
    onPageChange,
    onPerPageChange,
}: SimpleTablePaginationProps) {
    if (total <= 0) {
        return null;
    }

    return (
        <Card
            className={cn(
                'flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between',
                className,
            )}
        >
            <p className="text-xs text-slate-500 dark:text-slate-400">
                {from != null && to != null ? (
                    <>
                        نمایش {from.toLocaleString('fa-IR')} تا {to.toLocaleString('fa-IR')} از{' '}
                        {total.toLocaleString('fa-IR')} {itemLabel}
                    </>
                ) : (
                    <>مجموع {total.toLocaleString('fa-IR')} {itemLabel}</>
                )}
            </p>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                <div className="flex items-center gap-2">
                    <span className="shrink-0 text-xs text-slate-500 dark:text-slate-400">تعداد در صفحه</span>
                    <select
                        value={perPage || DEFAULT_PAGE_SIZE}
                        disabled={disabled}
                        onChange={(e) => onPerPageChange(Number(e.target.value))}
                        className="h-8 rounded-md border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950"
                    >
                        {PAGE_SIZE_OPTIONS.map((size) => (
                            <option key={size} value={size}>
                                {size.toLocaleString('fa-IR')} تایی
                            </option>
                        ))}
                    </select>
                </div>

                <div className="flex items-center justify-between gap-2 sm:justify-end">
                    <AdminButton
                        type="button"
                        variant="outline"
                        disabled={disabled || currentPage <= 1}
                        onClick={() => onPageChange(currentPage - 1)}
                        className="h-8 px-3 text-xs"
                    >
                        قبلی
                    </AdminButton>
                    <span className="text-xs text-slate-500 dark:text-slate-400">
                        صفحه {currentPage.toLocaleString('fa-IR')} از {Math.max(lastPage, 1).toLocaleString('fa-IR')}
                    </span>
                    <AdminButton
                        type="button"
                        variant="outline"
                        disabled={disabled || currentPage >= lastPage}
                        onClick={() => onPageChange(currentPage + 1)}
                        className="h-8 px-3 text-xs"
                    >
                        بعدی
                    </AdminButton>
                </div>
            </div>
        </Card>
    );
}
