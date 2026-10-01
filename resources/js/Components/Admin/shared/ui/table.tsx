import * as React from "react";

import { cn } from "@/lib/utils";

const Table = React.forwardRef<
  HTMLTableElement,
  React.HTMLAttributes<HTMLTableElement>
>(({ className, ...props }, ref) => (
  <div className="w-full overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <table
      ref={ref}
      className={cn("w-full caption-bottom text-sm", className)}
      {...props}
    />
  </div>
));

Table.displayName = "Table";

const TableHeader = React.forwardRef<
  HTMLTableSectionElement,
  React.HTMLAttributes<HTMLTableSectionElement>
>(({ className, ...props }, ref) => (
  <thead
    ref={ref}
    className={cn(
      "border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950",
      className,
    )}
    {...props}
  />
));

TableHeader.displayName = "TableHeader";

const TableBody = React.forwardRef<
  HTMLTableSectionElement,
  React.HTMLAttributes<HTMLTableSectionElement>
>(({ className, ...props }, ref) => (
  <tbody
    ref={ref}
    className={cn("divide-y divide-slate-200 dark:divide-slate-800", className)}
    {...props}
  />
));

TableBody.displayName = "TableBody";

const TableFooter = React.forwardRef<
  HTMLTableSectionElement,
  React.HTMLAttributes<HTMLTableSectionElement>
>(({ className, ...props }, ref) => (
  <tfoot
    ref={ref}
    className={cn(
      "border-t border-slate-200 bg-slate-50 font-medium dark:border-slate-800 dark:bg-slate-950",
      className,
    )}
    {...props}
  />
));

TableFooter.displayName = "TableFooter";

const TableRow = React.forwardRef<
  HTMLTableRowElement,
  React.HTMLAttributes<HTMLTableRowElement>
>(({ className, ...props }, ref) => (
  <tr
    ref={ref}
    className={cn(
      "transition-colors hover:bg-slate-50 dark:hover:bg-slate-950/40",
      className,
    )}
    {...props}
  />
));

TableRow.displayName = "TableRow";

const TableHead = React.forwardRef<
  HTMLTableCellElement,
  React.ThHTMLAttributes<HTMLTableCellElement>
>(({ className, ...props }, ref) => (
  <th
    ref={ref}
    className={cn(
      "h-12 px-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400",
      className,
    )}
    {...props}
  />
));

TableHead.displayName = "TableHead";

const TableCell = React.forwardRef<
  HTMLTableCellElement,
  React.TdHTMLAttributes<HTMLTableCellElement>
>(({ className, ...props }, ref) => (
  <td
    ref={ref}
    className={cn(
      "px-4 py-4 align-middle text-sm text-slate-700 dark:text-slate-300",
      className,
    )}
    {...props}
  />
));

TableCell.displayName = "TableCell";

interface TableEmptyStateProps {
  colSpan: number;
  title?: string;
  description?: string;
}

function TableEmptyState({
  colSpan,
  title = "داده‌ای برای نمایش وجود ندارد",
  description = "با تغییر فیلترها یا افزودن داده جدید دوباره تلاش کنید.",
}: TableEmptyStateProps) {
  return (
    <TableRow className="hover:bg-transparent dark:hover:bg-transparent">
      <TableCell colSpan={colSpan} className="px-6 py-14 text-center">
        <div className="space-y-1">
          <p className="text-sm font-medium text-slate-700 dark:text-slate-300">
            {title}
          </p>
          <p className="text-xs text-slate-500 dark:text-slate-400">
            {description}
          </p>
        </div>
      </TableCell>
    </TableRow>
  );
}

export {
  Table,
  TableBody,
  TableCell,
  TableEmptyState,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
};
