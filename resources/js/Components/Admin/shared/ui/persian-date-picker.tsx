import * as React from "react";
import {
  CalendarDays,
  ChevronLeft,
  ChevronRight,
  X,
} from "lucide-react";
import { cn } from "@/lib/utils";
import { PopoverPanel } from "@/Components/Admin/shared/ui/popover-panel";
import {
  JALALI_MONTH_NAMES,
  JALALI_WEEKDAY_SHORT,
  JalaliDateTime,
  addJalaliMonths,
  buildJalaliMonthGrid,
  formatIsoToJalali,
  isoToJalali,
  isIsoAfter,
  isIsoBefore,
  toPersianDigits,
} from "@/Components/Admin/shared/lib/jalali-calendar";
import {
  joinIsoDateTime,
  splitIsoDateTime,
  toGregorianDateOnly,
} from "@/Components/Admin/shared/lib/datetime-local-value";

export type PersianDatePickerProps = {
  /**
   * بدون `includeTime`: `YYYY-MM-DD`
   * با `includeTime`: `YYYY-MM-DDTHH:mm`
   */
  value?: string;
  onChange?: (value: string) => void;
  /** انتخاب ساعت داخل همان پاپ‌اور (بدون فیلد جدا). */
  includeTime?: boolean;
  /** ساعت پیش‌فرض هنگام انتخاب روز جدید (پیش‌فرض ۰۹:۰۰). */
  defaultTime?: string;
  placeholder?: string;
  label?: string;
  disabled?: boolean;
  error?: string;
  className?: string;
  containerClassName?: string;
  min?: string;
  max?: string;
  clearable?: boolean;
  align?: "start" | "end" | "center";
  placement?: "top" | "bottom" | "auto";
  size?: "default" | "compact";
  /** On mobile, open as a full-screen sheet. Defaults to true. */
  fullScreenOnMobile?: boolean;
};

export function PersianDatePicker({
  value = "",
  onChange,
  placeholder = "انتخاب تاریخ",
  label,
  disabled = false,
  error,
  className,
  containerClassName,
  min,
  max,
  clearable = true,
  align = "start",
  placement = "bottom",
  size = "default",
  fullScreenOnMobile = true,
  includeTime = false,
  defaultTime = "09:00",
}: PersianDatePickerProps) {
  const isCompact = size === "compact";
  const now = JalaliDateTime.now();
  const todayIso = now.toIsoDate();
  const dateValue = toGregorianDateOnly(value);

  const selectedJalali = React.useMemo(
    () => (dateValue ? isoToJalali(dateValue) : null),
    [dateValue],
  );

  const [isOpen, setIsOpen] = React.useState(false);
  const [viewYear, setViewYear] = React.useState(now.year);
  const [viewMonth, setViewMonth] = React.useState(now.month);
  const [pendingDate, setPendingDate] = React.useState("");
  const [pendingTime, setPendingTime] = React.useState(defaultTime);

  React.useEffect(() => {
    if (!isOpen) return;

    const anchor = selectedJalali ?? {
      year: now.year,
      month: now.month,
      day: now.day,
    };

    setViewYear(anchor.year);
    setViewMonth(anchor.month);
  }, [isOpen, selectedJalali, now.year, now.month, now.day]);

  React.useEffect(() => {
    if (!isOpen || !includeTime) {
      return;
    }

    const { date, time } = splitIsoDateTime(value);
    setPendingDate(date);
    setPendingTime(time || defaultTime);
  }, [isOpen, includeTime, value, defaultTime]);

  const monthCells = React.useMemo(
    () => buildJalaliMonthGrid(viewYear, viewMonth),
    [viewYear, viewMonth],
  );

  const yearOptions = React.useMemo(() => {
    const center = viewYear;
    return Array.from({ length: 21 }, (_, index) => center - 10 + index);
  }, [viewYear]);

  const { time: valueTime } = splitIsoDateTime(value);
  const displayValue = React.useMemo(() => {
    if (!dateValue) {
      return "";
    }

    const jalali = formatIsoToJalali(dateValue, "long");
    if (!includeTime || !valueTime) {
      return jalali;
    }

    return `${jalali} — ${toPersianDigits(valueTime)}`;
  }, [dateValue, includeTime, valueTime]);

  const handleSelect = (iso: string) => {
    const normalized = JalaliDateTime.fromIsoDate(iso)?.toIsoDate() ?? iso;

    if (includeTime) {
      setPendingDate(normalized);
      if (!pendingTime) {
        setPendingTime(defaultTime);
      }
      return;
    }

    onChange?.(normalized);
    setIsOpen(false);
  };

  const handleConfirmDateTime = () => {
    if (!pendingDate) {
      return;
    }

    onChange?.(joinIsoDateTime(pendingDate, pendingTime || defaultTime));
    setIsOpen(false);
  };

  const handleClear = (event: React.MouseEvent) => {
    event.stopPropagation();
    onChange?.("");
  };

  const goToPreviousMonth = () => {
    const next = addJalaliMonths(viewYear, viewMonth, -1);
    setViewYear(next.year);
    setViewMonth(next.month);
  };

  const goToNextMonth = () => {
    const next = addJalaliMonths(viewYear, viewMonth, 1);
    setViewYear(next.year);
    setViewMonth(next.month);
  };

  const goToTodayMonth = () => {
    const current = JalaliDateTime.now();
    setViewYear(current.year);
    setViewMonth(current.month);
  };

  const minDate = min ? toGregorianDateOnly(min) : undefined;
  const maxDate = max ? toGregorianDateOnly(max) : undefined;

  const isDayDisabled = (iso: string) => {
    if (minDate && isIsoBefore(iso, minDate)) return true;
    if (maxDate && isIsoAfter(iso, maxDate)) return true;
    return false;
  };

  const selectedDateIso = includeTime && isOpen ? pendingDate || dateValue : dateValue;

  const canSelectToday = !isDayDisabled(todayIso);

  const viewingCurrentMonth =
    viewYear === now.year && viewMonth === now.month;

  return (
    <div className={cn("persian-date-picker w-full space-y-1.5 text-right", containerClassName)} dir="rtl">
      {label && (
        <label className="text-xs font-semibold text-slate-700 dark:text-slate-300">
          {label}
        </label>
      )}

      <PopoverPanel
        isOpen={isOpen}
        onOpenChange={setIsOpen}
        align={align}
        placement={placement}
        title={isCompact ? undefined : includeTime ? "انتخاب تاریخ و ساعت" : "انتخاب تاریخ"}
        fullScreenOnMobile={fullScreenOnMobile}
        panelClassName={cn(
          "overflow-hidden border border-[rgb(var(--primary)/0.18)] shadow-lg shadow-[rgb(var(--primary)/0.12)] ring-1 ring-[rgb(var(--primary)/0.08)]",
          isCompact
            ? "w-[min(calc(100vw-2rem),17.5rem)]"
            : "w-[min(calc(100vw-2rem),21rem)]",
        )}
        trigger={
          <button
            type="button"
            disabled={disabled}
            aria-label={label ?? placeholder}
            className={cn(
              "flex h-10 w-full items-center justify-between gap-2 rounded-md border bg-white px-3 text-sm transition-colors",
              "border-[rgb(var(--primary)/0.28)] text-slate-900",
              "hover:border-[rgb(var(--primary)/0.45)] hover:bg-[rgb(var(--primary)/0.03)]",
              "focus-visible:border-[rgb(var(--primary))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--primary)/0.22)]",
              "disabled:cursor-not-allowed disabled:opacity-60",
              "dark:border-[rgb(var(--primary)/0.35)] dark:bg-slate-950 dark:text-slate-100",
              "dark:hover:border-[rgb(var(--primary)/0.5)] dark:hover:bg-[rgb(var(--primary)/0.06)]",
              "dark:focus-visible:ring-[rgb(var(--primary)/0.28)]",
              error && "border-rose-500 dark:border-rose-500",
              isOpen &&
                "border-[rgb(var(--primary))] bg-[rgb(var(--primary)/0.04)] ring-2 ring-[rgb(var(--primary)/0.2)] dark:bg-[rgb(var(--primary)/0.08)]",
              className,
            )}
          >
            <span
              className={cn(
                "min-w-0 flex-1 truncate text-right",
                displayValue
                  ? "font-medium text-[rgb(var(--primary))] dark:text-[rgb(var(--primary)/0.95)]"
                  : "text-slate-400 dark:text-slate-500",
              )}
            >
              {displayValue || placeholder}
            </span>

            <span className="flex shrink-0 items-center gap-1">
              {clearable && value && !disabled && (
                <span
                  role="button"
                  tabIndex={0}
                  aria-label="پاک کردن تاریخ"
                  onClick={handleClear}
                  onKeyDown={(event) => {
                    if (event.key === "Enter" || event.key === " ") {
                      event.preventDefault();
                      handleClear(event as unknown as React.MouseEvent);
                    }
                  }}
                  className="rounded-md p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                >
                  <X className="h-3.5 w-3.5" />
                </span>
              )}
              <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[rgb(var(--primary)/0.1)] text-[rgb(var(--primary))]">
                <CalendarDays className="h-4 w-4" />
              </span>
            </span>
          </button>
        }
      >
        <div className="overflow-hidden max-md:flex max-md:min-h-full max-md:flex-col">
          <div
            className={cn(
              "border-b border-[rgb(var(--primary)/0.2)] bg-gradient-to-l from-[rgb(var(--primary)/0.14)] via-[rgb(var(--primary)/0.08)] to-[rgb(var(--primary)/0.02)] dark:from-[rgb(var(--primary)/0.12)] dark:via-[rgb(var(--primary)/0.06)] dark:to-transparent",
              isCompact ? "px-3 py-2" : "px-4 py-3",
              "max-md:px-5 max-md:py-4",
            )}
          >
            <p className="text-[11px] font-semibold text-[rgb(var(--primary))]">
              امروز
            </p>
            <p
              className={cn(
                "mt-1 font-semibold text-slate-900 dark:text-slate-100",
                isCompact ? "text-xs" : "text-sm",
              )}
            >
              {now.format("full")}
            </p>
            {!isCompact && (
              <p className="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                {now.format("short")}
              </p>
            )}
          </div>

          <div
            className={cn(
              "max-md:flex max-md:flex-1 max-md:flex-col max-md:p-5",
              isCompact ? "p-3" : "p-4",
            )}
          >
            <div
              className={cn(
                "flex items-center justify-between gap-2",
                isCompact ? "mb-2" : "mb-4",
              )}
            >
              <button
                type="button"
                onClick={goToPreviousMonth}
                className="rounded-lg p-2 text-[rgb(var(--primary)/0.75)] transition-colors hover:bg-[rgb(var(--primary)/0.1)] hover:text-[rgb(var(--primary))] dark:hover:bg-[rgb(var(--primary)/0.15)]"
                aria-label="ماه قبل"
              >
                <ChevronRight className="h-4 w-4" />
              </button>

              <div className="flex min-w-0 flex-1 items-center justify-center gap-2">
                <select
                  value={viewMonth}
                  onChange={(event) => setViewMonth(Number(event.target.value))}
                className={cn(
                  "truncate rounded-lg border border-slate-200 bg-white px-2 font-medium text-slate-900 outline-none focus:border-[rgb(var(--primary)/0.5)] focus:ring-2 focus:ring-[rgb(var(--primary)/0.15)] dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 max-md:max-w-none max-md:py-2",
                  isCompact
                    ? "max-w-[6.5rem] py-1 text-xs"
                    : "max-w-[7.5rem] py-1.5 text-sm",
                )}
                  aria-label="انتخاب ماه"
                >
                  {JALALI_MONTH_NAMES.map((monthName, index) => (
                    <option key={monthName} value={index + 1}>
                      {monthName}
                    </option>
                  ))}
                </select>

                <select
                  value={viewYear}
                  onChange={(event) => setViewYear(Number(event.target.value))}
                  className={cn(
                    "rounded-lg border border-slate-200 bg-white px-2 font-medium text-slate-900 outline-none focus:border-[rgb(var(--primary)/0.5)] focus:ring-2 focus:ring-[rgb(var(--primary)/0.15)] dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 max-md:py-2",
                    isCompact ? "py-1 text-xs" : "py-1.5 text-sm",
                  )}
                  aria-label="انتخاب سال"
                >
                  {yearOptions.map((year) => (
                    <option key={year} value={year}>
                      {toPersianDigits(year)}
                    </option>
                  ))}
                </select>
              </div>

              <button
                type="button"
                onClick={goToNextMonth}
                className="rounded-lg p-2 text-[rgb(var(--primary)/0.75)] transition-colors hover:bg-[rgb(var(--primary)/0.1)] hover:text-[rgb(var(--primary))] dark:hover:bg-[rgb(var(--primary)/0.15)]"
                aria-label="ماه بعد"
              >
                <ChevronLeft className="h-4 w-4" />
              </button>
            </div>

            <div className={cn(isCompact ? "mb-2 h-7" : "mb-3 h-8")}>
              {!viewingCurrentMonth ? (
                <button
                  type="button"
                  onClick={goToTodayMonth}
                  className="h-full w-full rounded-lg border border-dashed border-[rgb(var(--primary)/0.35)] px-3 text-xs font-medium text-[rgb(var(--primary))] transition-colors hover:bg-[rgb(var(--primary)/0.08)]"
                >
                  بازگشت به {now.getMonthName()} {toPersianDigits(now.year)}
                </button>
              ) : null}
            </div>

            <div
              className={cn(
                "mb-2 grid grid-cols-7 gap-1",
                isCompact ? "gap-0.5" : "gap-1",
              )}
            >
              {JALALI_WEEKDAY_SHORT.map((weekday) => (
                <div
                  key={weekday}
                  className="py-1 text-center text-[11px] font-bold text-[rgb(var(--primary)/0.65)] dark:text-[rgb(var(--primary)/0.75)]"
                >
                  {weekday}
                </div>
              ))}
            </div>

            <div
              className={cn(
                "grid grid-cols-7 content-start max-md:h-auto max-md:flex-1 max-md:gap-2",
                isCompact ? "h-[11.5rem] gap-0.5" : "h-[14.75rem] gap-1",
              )}
            >
              {monthCells.map((cell, index) => {
                if (!cell) {
                  return (
                    <div
                      key={`empty-${index}`}
                      className={cn(isCompact ? "h-8" : "h-9", "max-md:h-12")}
                    />
                  );
                }

                const isSelected = selectedDateIso ? cell.iso === selectedDateIso : false;
                const isToday = cell.iso === todayIso;
                const dayDisabled = isDayDisabled(cell.iso);

                return (
                  <button
                    key={cell.iso}
                    type="button"
                    disabled={dayDisabled}
                    onClick={() => handleSelect(cell.iso)}
                    aria-label={
                      isToday
                        ? `امروز ${toPersianDigits(cell.day)}`
                        : toPersianDigits(cell.day)
                    }
                    aria-current={isToday ? "date" : undefined}
                    className={cn(
                      "relative flex items-center justify-center rounded-lg font-medium transition-all max-md:h-12 max-md:text-base",
                      isCompact
                        ? "h-8 text-xs"
                        : "h-9 text-sm",
                      isSelected
                        ? "bg-[rgb(var(--primary))] text-[rgb(var(--primary-foreground))] shadow-sm shadow-[rgb(var(--primary)/0.35)]"
                        : "text-slate-700 hover:bg-[rgb(var(--primary)/0.08)] hover:text-[rgb(var(--primary))] dark:text-slate-200 dark:hover:bg-[rgb(var(--primary)/0.12)]",
                      isToday &&
                        !isSelected &&
                        "bg-[rgb(var(--primary)/0.12)] font-bold text-[rgb(var(--primary))] ring-1 ring-[rgb(var(--primary)/0.45)] ring-inset",
                      dayDisabled &&
                        "cursor-not-allowed opacity-30 hover:bg-transparent dark:hover:bg-transparent",
                    )}
                  >
                    {toPersianDigits(cell.day)}
                    {isToday && !isSelected && (
                      <span className="absolute bottom-1 h-1 w-1 rounded-full bg-[rgb(var(--primary))]" />
                    )}
                  </button>
                );
              })}
            </div>

            {includeTime ? (
              <div
                className={cn(
                  "border-t border-slate-100 dark:border-slate-800",
                  isCompact ? "mt-3 pt-3" : "mt-4 pt-3",
                )}
              >
                <p className="mb-2 text-center text-xs font-semibold text-slate-600 dark:text-slate-400">
                  ساعت
                </p>
                <input
                  type="time"
                  dir="ltr"
                  disabled={!pendingDate && !dateValue}
                  value={pendingTime}
                  onChange={(event) => setPendingTime(event.target.value)}
                  className={cn(
                    "mx-auto flex h-10 w-full max-w-[10rem] rounded-lg border border-slate-200 bg-white px-3 text-center text-sm text-slate-900 outline-none focus:border-[rgb(var(--primary)/0.5)] focus:ring-2 focus:ring-[rgb(var(--primary)/0.15)] dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100",
                  )}
                />
              </div>
            ) : null}

            <div
              className={cn(
                "flex items-center justify-between border-t border-slate-100 dark:border-slate-800 max-md:mt-auto max-md:pt-4",
                isCompact ? "mt-3 pt-2" : "mt-4 pt-3",
              )}
            >
              {includeTime ? (
                <button
                  type="button"
                  disabled={!pendingDate}
                  onClick={handleConfirmDateTime}
                  className="rounded-lg bg-[rgb(var(--primary))] px-3 py-1.5 text-xs font-semibold text-[rgb(var(--primary-foreground))] transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  تأیید
                </button>
              ) : (
                <button
                  type="button"
                  disabled={!canSelectToday}
                  onClick={() => handleSelect(todayIso)}
                  className="rounded-lg bg-[rgb(var(--primary))] px-3 py-1.5 text-xs font-semibold text-[rgb(var(--primary-foreground))] transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  انتخاب امروز
                </button>
              )}

              {includeTime ? (
                <button
                  type="button"
                  disabled={!canSelectToday}
                  onClick={() => {
                    const d = new Date();
                    const t = `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
                    setPendingDate(todayIso);
                    setPendingTime(t);
                  }}
                  className="rounded-lg border border-[rgb(var(--primary)/0.35)] px-3 py-1.5 text-xs font-medium text-[rgb(var(--primary))] transition-colors hover:bg-[rgb(var(--primary)/0.08)]"
                >
                  امروز
                </button>
              ) : null}

              {clearable && value ? (
                <button
                  type="button"
                  onClick={() => {
                    onChange?.("");
                    setPendingDate("");
                    setPendingTime(defaultTime);
                  }}
                  className="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                >
                  پاک کردن
                </button>
              ) : (
                <span className="invisible px-3 py-1.5 text-xs" aria-hidden>
                  پاک کردن
                </span>
              )}
            </div>
          </div>
        </div>
      </PopoverPanel>

      {error && (
        <p className="text-xs text-rose-500 dark:text-rose-400">{error}</p>
      )}
    </div>
  );
}
