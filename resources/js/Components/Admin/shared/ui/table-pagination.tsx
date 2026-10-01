import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Select } from '@/Components/Admin/shared/ui/select';
import { DEFAULT_PAGE_SIZE, PAGE_SIZE_OPTIONS } from '@/Components/Admin/shared/config/pagination';
import { cn } from "@/lib/utils";

export type TablePaginationProps = {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
  from: number | null;
  to: number | null;
  disabled?: boolean;
  /** e.g. «محصول»، «رویداد» — default: مورد */
  itemLabel?: string;
  className?: string;
  onPageChange: (page: number) => void;
  onPerPageChange: (perPage: number) => void;
};

const pageSizeOptions = PAGE_SIZE_OPTIONS.map((value) => ({
  value: String(value),
  label: `${value.toLocaleString("fa-IR")} تایی`,
}));

export function TablePagination({
  currentPage,
  lastPage,
  total,
  perPage,
  from,
  to,
  disabled = false,
  itemLabel = "مورد",
  className,
  onPageChange,
  onPerPageChange,
}: TablePaginationProps) {
  if (total <= 0) return null;

  const selectedSize =
    pageSizeOptions.find((option) => option.value === String(perPage)) ?? {
      value: String(perPage || DEFAULT_PAGE_SIZE),
      label: `${(perPage || DEFAULT_PAGE_SIZE).toLocaleString("fa-IR")} تایی`,
    };

  return (
    <Card
      className={cn(
        "flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between",
        className,
      )}
    >
      <p className="text-xs text-slate-500 dark:text-slate-400">
        {from != null && to != null ? (
          <>
            نمایش {from.toLocaleString("fa-IR")} تا {to.toLocaleString("fa-IR")}{" "}
            از {total.toLocaleString("fa-IR")} {itemLabel}
          </>
        ) : (
          <>مجموع {total.toLocaleString("fa-IR")} {itemLabel}</>
        )}
      </p>

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
        <div className="flex items-center gap-2">
          <span className="shrink-0 text-xs text-slate-500 dark:text-slate-400">
            تعداد در صفحه
          </span>
          <div className="min-w-[7.5rem]">
            <Select
              options={pageSizeOptions}
              value={selectedSize}
              onChange={(selected) => {
                const next = Number(selected?.value ?? DEFAULT_PAGE_SIZE);
                if (!Number.isFinite(next) || next === perPage) return;
                onPerPageChange(next);
              }}
              isSearchable={false}
              isDisabled={disabled}
              placeholder="تعداد"
            />
          </div>
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
            صفحه {currentPage.toLocaleString("fa-IR")} از{" "}
            {Math.max(lastPage, 1).toLocaleString("fa-IR")}
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
