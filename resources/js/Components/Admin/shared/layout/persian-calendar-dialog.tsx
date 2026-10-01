"use client";

import { useEffect, useMemo, useState } from "react";
import {
  ChevronLeft,
  ChevronRight,
  MoonStar,
  Sparkles,
  SunMedium,
} from "lucide-react";
import { Dialog } from "@/Components/Admin/shared/ui/dialog";
import { cn } from "@/lib/utils";
import {
  JALALI_MONTH_NAMES,
  JALALI_WEEKDAY_SHORT,
  JalaliDateTime,
  addJalaliMonths,
  buildJalaliMonthGrid,
  toPersianDigits,
} from "@/Components/Admin/shared/lib/jalali-calendar";
import {
  formatHolidayDate,
  getIranHoliday,
  getIranHolidaysForMonth,
  getUpcomingIranHolidays,
  isIranFriday,
} from "@/Components/Admin/shared/lib/iran-holidays";
import { formatPersianDateTime } from "@/Components/Admin/shared/lib/format-persian-datetime";
import type { IranHoliday } from "@/Components/Admin/shared/config/iran-holidays";

type PersianCalendarDialogProps = {
  isOpen: boolean;
  onClose: () => void;
};

export function PersianCalendarDialog({
  isOpen,
  onClose,
}: PersianCalendarDialogProps) {
  const today = JalaliDateTime.now();
  const [viewYear, setViewYear] = useState(today.year);
  const [viewMonth, setViewMonth] = useState(today.month);
  const [selectedDay, setSelectedDay] = useState(today.day);
  const [now, setNow] = useState(() => new Date());

  useEffect(() => {
    if (!isOpen) return;
    setViewYear(today.year);
    setViewMonth(today.month);
    setSelectedDay(today.day);
  }, [isOpen, today.year, today.month, today.day]);

  useEffect(() => {
    if (!isOpen) return;
    const id = window.setInterval(() => setNow(new Date()), 1000);
    return () => window.clearInterval(id);
  }, [isOpen]);

  const grid = useMemo(
    () => buildJalaliMonthGrid(viewYear, viewMonth),
    [viewYear, viewMonth],
  );

  const monthHolidays = useMemo(
    () => getIranHolidaysForMonth(viewYear, viewMonth),
    [viewYear, viewMonth],
  );

  const upcoming = useMemo(() => getUpcomingIranHolidays(today, 5), [today]);

  const selectedHoliday = getIranHoliday(viewYear, viewMonth, selectedDay);
  const selectedIsFriday = isIranFriday(viewYear, viewMonth, selectedDay);
  const selectedIsToday =
    viewYear === today.year &&
    viewMonth === today.month &&
    selectedDay === today.day;

  const goMonth = (offset: number) => {
    const next = addJalaliMonths(viewYear, viewMonth, offset);
    setViewYear(next.year);
    setViewMonth(next.month);
    setSelectedDay(1);
  };

  const goToday = () => {
    setViewYear(today.year);
    setViewMonth(today.month);
    setSelectedDay(today.day);
  };

  return (
    <Dialog
      isOpen={isOpen}
      onClose={onClose}
      hideHeader
      panelClassName="max-w-4xl overflow-hidden"
    >
      <div className="relative">
        <button
          type="button"
          onClick={onClose}
          aria-label="بستن"
          className="absolute left-4 top-4 z-10 rounded-lg bg-white/10 p-1.5 text-white/80 backdrop-blur transition hover:bg-white/20 hover:text-white"
        >
          <svg
            className="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <div className="relative overflow-hidden border-b border-slate-200 bg-gradient-to-l from-slate-900 via-slate-800 to-teal-900 px-6 py-5 text-white dark:border-slate-800">
          <div className="pointer-events-none absolute -left-10 top-0 h-40 w-40 rounded-full bg-teal-400/20 blur-3xl" />
          <div className="pointer-events-none absolute -right-8 bottom-0 h-32 w-32 rounded-full bg-amber-300/10 blur-3xl" />

          <div className="relative flex flex-wrap items-end justify-between gap-4">
            <div>
              <p className="text-xs font-medium text-teal-200/90">امروز</p>
              <p className="mt-1 text-xl font-semibold tracking-tight">
                {today.format("full")}
              </p>
              <p className="mt-2 font-mono text-2xl tabular-nums text-white/95">
                {formatPersianDateTime(now, "time")}
              </p>
            </div>

            <div className="rounded-xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-sm">
              <p className="text-[11px] text-white/60">نزدیک‌ترین تعطیل رسمی</p>
              {upcoming[0] ? (
                <>
                  <p className="mt-1 text-sm font-semibold text-white">
                    {upcoming[0].title}
                  </p>
                  <p className="mt-0.5 text-xs text-teal-100/80">
                    {formatHolidayDate(upcoming[0])}
                  </p>
                </>
              ) : (
                <p className="mt-1 text-sm text-white/70">موردی ثبت نشده</p>
              )}
            </div>
          </div>
        </div>

        <div className="grid gap-0 lg:grid-cols-[1.35fr_1fr]">
          <div className="space-y-4 border-b border-slate-200 p-5 dark:border-slate-800 lg:border-b-0 lg:border-l">
            <div className="flex items-center justify-between gap-3">
              <button
                type="button"
                onClick={() => goMonth(1)}
                className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                aria-label="ماه بعد"
              >
                <ChevronRight className="h-4 w-4" />
              </button>

              <div className="text-center">
                <p className="text-base font-semibold text-slate-900 dark:text-slate-100">
                  {JALALI_MONTH_NAMES[viewMonth - 1]}{" "}
                  {toPersianDigits(viewYear)}
                </p>
                <button
                  type="button"
                  onClick={goToday}
                  className="mt-0.5 text-[11px] font-medium text-teal-700 hover:underline dark:text-teal-300"
                >
                  برو به امروز
                </button>
              </div>

              <button
                type="button"
                onClick={() => goMonth(-1)}
                className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                aria-label="ماه قبل"
              >
                <ChevronLeft className="h-4 w-4" />
              </button>
            </div>

            <div className="grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-slate-400 dark:text-slate-500">
              {JALALI_WEEKDAY_SHORT.map((label) => (
                <div key={label} className="py-1">
                  {label}
                </div>
              ))}
            </div>

            <div className="grid grid-cols-7 gap-1">
              {grid.map((cell, index) => {
                if (!cell) {
                  return <div key={`empty-${index}`} className="aspect-square" />;
                }

                const holiday = getIranHoliday(viewYear, viewMonth, cell.day);
                const friday = isIranFriday(viewYear, viewMonth, cell.day);
                const isSelected = cell.day === selectedDay;
                const isTodayCell =
                  viewYear === today.year &&
                  viewMonth === today.month &&
                  cell.day === today.day;
                const isOff = Boolean(holiday) || friday;

                return (
                  <button
                    key={cell.iso}
                    type="button"
                    onClick={() => setSelectedDay(cell.day)}
                    title={holiday?.title ?? (friday ? "جمعه" : undefined)}
                    className={cn(
                      "relative flex aspect-square flex-col items-center justify-center rounded-xl text-sm font-medium transition",
                      isSelected
                        ? "bg-slate-900 text-white shadow-sm dark:bg-slate-100 dark:text-slate-900"
                        : isOff
                          ? "bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20"
                          : "text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800",
                      isTodayCell &&
                        !isSelected &&
                        "ring-2 ring-teal-500/50 ring-offset-1 ring-offset-white dark:ring-offset-slate-900",
                    )}
                  >
                    <span className="font-mono tabular-nums">
                      {toPersianDigits(cell.day)}
                    </span>
                    {(holiday || friday) && (
                      <span
                        className={cn(
                          "mt-0.5 h-1 w-1 rounded-full",
                          isSelected ? "bg-white/80" : "bg-rose-500",
                        )}
                      />
                    )}
                  </button>
                );
              })}
            </div>

            <div className="flex flex-wrap gap-3 text-[11px] text-slate-500 dark:text-slate-400">
              <span className="inline-flex items-center gap-1.5">
                <span className="h-2.5 w-2.5 rounded bg-rose-100 ring-1 ring-rose-300 dark:bg-rose-500/20 dark:ring-rose-400/40" />
                تعطیل / جمعه
              </span>
              <span className="inline-flex items-center gap-1.5">
                <span className="h-2.5 w-2.5 rounded ring-2 ring-teal-500/60" />
                امروز
              </span>
            </div>
          </div>

          <div className="space-y-4 bg-slate-50/80 p-5 dark:bg-slate-950/40">
            <DayAnnouncement
              day={selectedDay}
              monthName={JALALI_MONTH_NAMES[viewMonth - 1] ?? ""}
              year={viewYear}
              holiday={selectedHoliday}
              isFriday={selectedIsFriday}
              isToday={selectedIsToday}
            />

            <div>
              <div className="mb-2 flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
                <Sparkles className="h-3.5 w-3.5 text-teal-600 dark:text-teal-400" />
                تعطیلات این ماه
              </div>

              {monthHolidays.length === 0 ? (
                <p className="rounded-xl border border-dashed border-slate-200 bg-white px-3 py-6 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                  در این ماه تعطیل رسمی ثبت نشده است.
                </p>
              ) : (
                <ul className="max-h-56 space-y-2 overflow-y-auto thin-scrollbar pe-1">
                  {monthHolidays.map((holiday) => (
                    <li key={holiday.key}>
                      <button
                        type="button"
                        onClick={() => setSelectedDay(holiday.day)}
                        className={cn(
                          "flex w-full items-start gap-3 rounded-xl border px-3 py-2.5 text-right transition",
                          selectedDay === holiday.day
                            ? "border-teal-300 bg-teal-50 dark:border-teal-500/30 dark:bg-teal-500/10"
                            : "border-slate-200 bg-white hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-600",
                        )}
                      >
                        <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 font-mono text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                          {toPersianDigits(holiday.day)}
                        </span>
                        <span className="min-w-0">
                          <span className="block text-sm font-medium text-slate-800 dark:text-slate-100">
                            {holiday.title}
                          </span>
                          <span className="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">
                            {holiday.kind === "religious"
                              ? "مناسبت مذهبی"
                              : "مناسبت ملی"}
                          </span>
                        </span>
                      </button>
                    </li>
                  ))}
                </ul>
              )}
            </div>

            {upcoming.length > 0 && (
              <div>
                <p className="mb-2 text-xs font-semibold text-slate-700 dark:text-slate-200">
                  تعطیلات پیش‌رو
                </p>
                <div className="flex flex-wrap gap-1.5">
                  {upcoming.map((holiday) => (
                    <button
                      key={holiday.key}
                      type="button"
                      onClick={() => {
                        setViewYear(holiday.year);
                        setViewMonth(holiday.month);
                        setSelectedDay(holiday.day);
                      }}
                      className="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] text-slate-600 transition hover:border-teal-300 hover:text-teal-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-teal-500/40 dark:hover:text-teal-200"
                    >
                      {toPersianDigits(holiday.day)}{" "}
                      {JALALI_MONTH_NAMES[holiday.month - 1]}
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        <p className="border-t border-slate-200 px-5 py-3 text-[11px] leading-5 text-slate-400 dark:border-slate-800 dark:text-slate-500">
          تعطیلات رسمی بر اساس اعلامیه‌های رایج تقویم ایران است؛ مناسبت‌های قمری
          ممکن است با رؤیت هلال یک روز جابه‌جا شوند.
        </p>
      </div>
    </Dialog>
  );
}

function DayAnnouncement({
  day,
  monthName,
  year,
  holiday,
  isFriday,
  isToday,
}: {
  day: number;
  monthName: string;
  year: number;
  holiday?: IranHoliday;
  isFriday: boolean;
  isToday: boolean;
}) {
  const isOff = Boolean(holiday) || isFriday;

  return (
    <div
      className={cn(
        "rounded-2xl border p-4",
        isOff
          ? "border-rose-200 bg-gradient-to-br from-rose-50 to-white dark:border-rose-500/20 dark:from-rose-500/10 dark:to-slate-900"
          : "border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900",
      )}
    >
      <div className="flex items-start gap-3">
        <div
          className={cn(
            "flex h-10 w-10 shrink-0 items-center justify-center rounded-xl",
            isOff
              ? "bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300"
              : "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300",
          )}
        >
          {holiday?.kind === "religious" ? (
            <MoonStar className="h-5 w-5" />
          ) : (
            <SunMedium className="h-5 w-5" />
          )}
        </div>

        <div className="min-w-0 flex-1">
          <p className="text-xs text-slate-500 dark:text-slate-400">
            {toPersianDigits(day)} {monthName} {toPersianDigits(year)}
            {isToday ? " · امروز" : ""}
          </p>

          {holiday ? (
            <>
              <p className="mt-1 text-base font-semibold text-slate-900 dark:text-slate-50">
                {holiday.title}
              </p>
              <p className="mt-1 text-xs leading-5 text-rose-700/90 dark:text-rose-300/90">
                تعطیل رسمی
                {isFriday ? " · مصادف با جمعه" : ""}
              </p>
            </>
          ) : isFriday ? (
            <>
              <p className="mt-1 text-base font-semibold text-slate-900 dark:text-slate-50">
                جمعه
              </p>
              <p className="mt-1 text-xs text-rose-700/90 dark:text-rose-300/90">
                تعطیل هفتگی
              </p>
            </>
          ) : (
            <>
              <p className="mt-1 text-base font-semibold text-slate-900 dark:text-slate-50">
                روز کاری
              </p>
              <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                تعطیل رسمی برای این روز ثبت نشده است.
              </p>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
