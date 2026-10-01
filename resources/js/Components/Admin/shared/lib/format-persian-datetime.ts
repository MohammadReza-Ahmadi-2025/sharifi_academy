import {
  JalaliDateTime,
  formatIsoToJalali,
  pad2,
  toPersianDigits,
} from "./jalali-calendar";

export type PersianDateTimeStyle = "table" | "long" | "short" | "time" | "timeShort" | "print";

const ISO_DATE_ONLY = /^\d{4}-\d{2}-\d{2}$/;
const ISO_DATE_TIME =
  /^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})?$/;

function hasExplicitTime(value: string): boolean {
  return /[T ]\d{2}:\d{2}/.test(value);
}

function parseApiDateTime(
  value: string | Date,
): { jalali: JalaliDateTime; hasTime: boolean } | null {
  if (value instanceof Date) {
    if (Number.isNaN(value.getTime())) return null;
    const jalali = JalaliDateTime.fromDate(value);
    const hasTime =
      value.getHours() !== 0 ||
      value.getMinutes() !== 0 ||
      value.getSeconds() !== 0 ||
      value.getMilliseconds() !== 0;
    return { jalali, hasTime };
  }

  const trimmed = value.trim();
  if (!trimmed) return null;

  if (ISO_DATE_ONLY.test(trimmed)) {
    const jalali = JalaliDateTime.fromIsoDate(trimmed);
    return jalali ? { jalali, hasTime: false } : null;
  }

  if (ISO_DATE_TIME.test(trimmed) || hasExplicitTime(trimmed)) {
    const parsed = new Date(trimmed);
    if (Number.isNaN(parsed.getTime())) return null;
    const jalali = JalaliDateTime.fromDate(parsed);
    return {
      jalali,
      hasTime:
        hasExplicitTime(trimmed) ||
        jalali.hour !== 0 ||
        jalali.minute !== 0 ||
        jalali.second !== 0,
    };
  }

  const parsed = new Date(trimmed);
  if (Number.isNaN(parsed.getTime())) return null;
  const jalali = JalaliDateTime.fromDate(parsed);
  return {
    jalali,
    hasTime:
      jalali.hour !== 0 || jalali.minute !== 0 || jalali.second !== 0,
  };
}

function formatJalaliDate(
  jalali: JalaliDateTime,
  style: "short" | "long",
): string {
  if (style === "short") {
    return jalali.format("short");
  }

  return `${toPersianDigits(jalali.day)} ${jalali.getMonthName()} ${toPersianDigits(jalali.year)}`;
}

function formatJalaliTime(jalali: JalaliDateTime, withSeconds = false): string {
  const time = withSeconds
    ? `${pad2(jalali.hour)}:${pad2(jalali.minute)}:${pad2(jalali.second)}`
    : `${pad2(jalali.hour)}:${pad2(jalali.minute)}`;
  return toPersianDigits(time);
}

/** Format a document date with its registration hour/minute when available. */
export function formatDocumentPrintDateTime(
  documentDate: string | null | undefined,
  registeredAt?: string | null,
): string {
  if (!documentDate?.trim()) return "—";

  const trimmedDate = documentDate.trim();

  if (!ISO_DATE_ONLY.test(trimmedDate)) {
    return formatPersianDateTime(trimmedDate, "print");
  }

  if (registeredAt?.trim()) {
    const datePart = formatPersianDateTime(trimmedDate, "short");
    const timePart = formatPersianDateTime(registeredAt, "timeShort");
    return `${datePart}، ${timePart}`;
  }

  return formatPersianDateTime(trimmedDate, "short");
}

/** Format invoice date for print: Jalali date + hour/minute when available. */
export function formatInvoicePrintDateTime(
  invoiceDate: string | null | undefined,
  registeredAt?: string | null,
): string {
  return formatDocumentPrintDateTime(invoiceDate, registeredAt);
}

/** Format a Gregorian API date (`YYYY-MM-DD`) as Jalali. */
export function formatGregorianApiDate(
  value: string | null | undefined,
  preset: "short" | "long" = "long",
): string {
  if (!value) return "—";
  return formatIsoToJalali(value, preset === "short" ? "short" : "long") || "—";
}

export function formatPersianDateTime(
  value: string | Date | null | undefined,
  style: PersianDateTimeStyle = "table",
): string {
  if (!value) return "—";

  const parsed = parseApiDateTime(value);
  if (!parsed) return "—";

  const { jalali, hasTime } = parsed;

  switch (style) {
    case "long":
      return hasTime
        ? `${jalali.getWeekdayName("full")}، ${formatJalaliDate(jalali, "long")}، ${formatJalaliTime(jalali)}`
        : `${jalali.getWeekdayName("full")}، ${formatJalaliDate(jalali, "long")}`;
    case "short":
      return formatJalaliDate(jalali, "short");
    case "time":
      return formatJalaliTime(jalali, true);
    case "timeShort":
      return formatJalaliTime(jalali, false);
    case "print":
      return `${formatJalaliDate(jalali, "short")}، ${formatJalaliTime(jalali, false)}`;
    case "table":
    default:
      return hasTime
        ? `${formatJalaliDate(jalali, "long")}، ${formatJalaliTime(jalali)}`
        : formatJalaliDate(jalali, "long");
  }
}

/** Shared class for Persian date/time in tables and detail views. */
export const persianDateTimeClassName =
  "whitespace-nowrap text-sm font-medium leading-6 text-slate-600 dark:text-slate-300";

/** Keep mono styling only for Latin technical values like IP. */
export const latinMonoClassName =
  "latin-digits font-mono text-sm text-slate-600 dark:text-slate-400";
