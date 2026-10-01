import {
  jalaaliMonthLength,
  jalaaliToDateObject,
  toGregorian,
  toJalaali,
} from "jalaali-js";

export type JalaliParts = {
  year: number;
  month: number;
  day: number;
};

export const JALALI_MONTH_NAMES = [
  "فروردین",
  "اردیبهشت",
  "خرداد",
  "تیر",
  "مرداد",
  "شهریور",
  "مهر",
  "آبان",
  "آذر",
  "دی",
  "بهمن",
  "اسفند",
] as const;

export const JALALI_WEEKDAY_SHORT = [
  "ش",
  "ی",
  "د",
  "س",
  "چ",
  "پ",
  "ج",
] as const;

/** Saturday = 0 — same convention as morilog/jalali */
export const JALALI_WEEKDAY_FULL = [
  "شنبه",
  "یکشنبه",
  "دوشنبه",
  "سه‌شنبه",
  "چهارشنبه",
  "پنجشنبه",
  "جمعه",
] as const;

export type JalaliFormatPreset =
  | "date"
  | "datetime"
  | "long"
  | "short"
  | "full"
  | "today";

export function toPersianDigits(value: string | number): string {
  return String(value).replace(/\d/g, (digit) => "۰۱۲۳۴۵۶۷۸۹"[Number(digit)]);
}

export function toLatinDigits(value: string): string {
  return String(value)
    .replace(/[۰-۹]/g, (digit) => "۰۱۲۳۴۵۶۷۸۹".indexOf(digit).toString())
    .replace(/[٠-٩]/g, (digit) => "٠١٢٣٤٥٦٧٨٩".indexOf(digit).toString());
}

export function pad2(value: number): string {
  return String(value).padStart(2, "0");
}

/** High-level Jalali date-time API inspired by morilog/jalali `Jalalian`. */
export class JalaliDateTime {
  readonly year: number;
  readonly month: number;
  readonly day: number;
  readonly hour: number;
  readonly minute: number;
  readonly second: number;

  constructor(
    year: number,
    month: number,
    day: number,
    hour = 0,
    minute = 0,
    second = 0,
  ) {
    this.year = year;
    this.month = month;
    this.day = day;
    this.hour = hour;
    this.minute = minute;
    this.second = second;
  }

  /** morilog: `Jalalian::now()` */
  static now(): JalaliDateTime {
    return JalaliDateTime.fromDate(new Date());
  }

  static fromDate(date: Date): JalaliDateTime {
    const { jy, jm, jd } = toJalaali(date);
    return new JalaliDateTime(
      jy,
      jm,
      jd,
      date.getHours(),
      date.getMinutes(),
      date.getSeconds(),
    );
  }

  static fromIsoDate(iso: string): JalaliDateTime | null {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso);
    if (!match) return null;

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);

    if (
      Number.isNaN(year) ||
      Number.isNaN(month) ||
      Number.isNaN(day)
    ) {
      return null;
    }

    return JalaliDateTime.fromDate(new Date(year, month - 1, day));
  }

  static fromJalali(year: number, month: number, day: number): JalaliDateTime {
    return new JalaliDateTime(year, month, day);
  }

  toDate(): Date {
    const { gy, gm, gd } = toGregorian(this.year, this.month, this.day);
    return new Date(gy, gm - 1, gd, this.hour, this.minute, this.second);
  }

  /** Gregorian ISO date for API (`Y-m-d`). */
  toIsoDate(): string {
    const { gy, gm, gd } = toGregorian(this.year, this.month, this.day);
    return `${gy}-${pad2(gm)}-${pad2(gd)}`;
  }

  /** morilog: `getDayOfWeek()` — Saturday = 0 */
  getDayOfWeek(): number {
    return (this.toDate().getDay() + 1) % 7;
  }

  getWeekdayName(style: "short" | "full" = "full"): string {
    const index = this.getDayOfWeek();
    return style === "short"
      ? JALALI_WEEKDAY_SHORT[index]
      : JALALI_WEEKDAY_FULL[index];
  }

  getMonthName(): string {
    return JALALI_MONTH_NAMES[this.month - 1] ?? "";
  }

  getMonthDays(): number {
    return jalaaliMonthLength(this.year, this.month);
  }

  isToday(): boolean {
    return this.isSameDay(JalaliDateTime.now());
  }

  isSameDay(other: JalaliDateTime): boolean {
    return (
      this.year === other.year &&
      this.month === other.month &&
      this.day === other.day
    );
  }

  isSameIsoDate(iso: string): boolean {
    const other = JalaliDateTime.fromIsoDate(iso);
    return other ? this.isSameDay(other) : false;
  }

  addMonths(offset: number): JalaliDateTime {
    let month = this.month + offset;
    let year = this.year;

    while (month > 12) {
      month -= 12;
      year += 1;
    }

    while (month < 1) {
      month += 12;
      year -= 1;
    }

    const day = Math.min(this.day, jalaaliMonthLength(year, month));

    return new JalaliDateTime(
      year,
      month,
      day,
      this.hour,
      this.minute,
      this.second,
    );
  }

  getFirstDayOfMonthWeekIndex(): number {
    const date = jalaaliToDateObject(this.year, this.month, 1);
    return (date.getDay() + 1) % 7;
  }

  format(preset: JalaliFormatPreset = "long"): string {
    switch (preset) {
      case "date":
        return `${this.year}-${pad2(this.month)}-${pad2(this.day)}`;
      case "datetime":
        return `${this.format("date")} ${pad2(this.hour)}:${pad2(this.minute)}:${pad2(this.second)}`;
      case "short":
        return toPersianDigits(
          `${this.year}/${pad2(this.month)}/${pad2(this.day)}`,
        );
      case "full":
      case "today":
        return `${this.getWeekdayName("full")}، ${toPersianDigits(this.day)} ${this.getMonthName()} ${toPersianDigits(this.year)}`;
      case "long":
      default:
        return `${toPersianDigits(this.day)} ${this.getMonthName()} ${toPersianDigits(this.year)}`;
    }
  }
}

export function formatIsoToJalali(
  iso: string,
  preset: JalaliFormatPreset = "long",
): string {
  const date = JalaliDateTime.fromIsoDate(iso);
  return date ? date.format(preset) : "";
}

export function getTodayIso(): string {
  return JalaliDateTime.now().toIsoDate();
}

export function getTodayJalali(): JalaliParts {
  const now = JalaliDateTime.now();
  return { year: now.year, month: now.month, day: now.day };
}

export function isoToJalali(iso: string): JalaliParts | null {
  const date = JalaliDateTime.fromIsoDate(iso);
  if (!date) return null;

  return {
    year: date.year,
    month: date.month,
    day: date.day,
  };
}

export function jalaliToIso(jy: number, jm: number, jd: number): string {
  return JalaliDateTime.fromJalali(jy, jm, jd).toIsoDate();
}

/** Normalize any supported date input to Gregorian `YYYY-MM-DD` for API requests. */
export function toGregorianApiDate(value: string): string | null {
  const trimmed = value.trim();
  if (!trimmed) return null;

  if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
    return JalaliDateTime.fromIsoDate(trimmed)?.toIsoDate() ?? null;
  }

  const latin = toLatinDigits(trimmed);
  const slashMatch = /^(\d{4})\/(\d{1,2})\/(\d{1,2})$/.exec(latin);
  if (slashMatch) {
    const year = Number(slashMatch[1]);
    const month = Number(slashMatch[2]);
    const day = Number(slashMatch[3]);

    if (year >= 1300 && year <= 1500) {
      return JalaliDateTime.fromJalali(year, month, day).toIsoDate();
    }

    if (year >= 1900 && year <= 2100) {
      return JalaliDateTime.fromDate(new Date(year, month - 1, day)).toIsoDate();
    }
  }

  return null;
}

export function isIsoBefore(a: string, b: string): boolean {
  return a < b;
}

export function isIsoAfter(a: string, b: string): boolean {
  return a > b;
}

export function isSameIso(a: string, b: string): boolean {
  return a === b;
}

export function addJalaliMonths(
  jy: number,
  jm: number,
  offset: number,
): Pick<JalaliParts, "year" | "month"> {
  const next = JalaliDateTime.fromJalali(jy, jm, 1).addMonths(offset);
  return { year: next.year, month: next.month };
}

export function buildJalaliMonthGrid(jy: number, jm: number) {
  const month = JalaliDateTime.fromJalali(jy, jm, 1);
  const daysInMonth = month.getMonthDays();
  const leadingEmpty = month.getFirstDayOfMonthWeekIndex();
  const cells: Array<{ day: number; iso: string } | null> = [];

  for (let index = 0; index < leadingEmpty; index += 1) {
    cells.push(null);
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    cells.push({
      day,
      iso: JalaliDateTime.fromJalali(jy, jm, day).toIsoDate(),
    });
  }

  // Always 6 weeks so month panels keep a stable height.
  while (cells.length < 42) {
    cells.push(null);
  }

  return cells;
}

export { jalaaliMonthLength, toGregorian, toJalaali };
