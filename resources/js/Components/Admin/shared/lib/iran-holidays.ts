import {
  IRAN_HOLIDAYS,
  type IranHoliday,
} from "@/Components/Admin/shared/config/iran-holidays";
import { JalaliDateTime, pad2 } from "@/Components/Admin/shared/lib/jalali-calendar";

function jalaliKey(year: number, month: number, day: number): string {
  return `${year}-${pad2(month)}-${pad2(day)}`;
}

const holidayByKey = new Map(
  IRAN_HOLIDAYS.map((holiday) => [holiday.key, holiday]),
);

export function getIranHoliday(
  year: number,
  month: number,
  day: number,
): IranHoliday | undefined {
  return holidayByKey.get(jalaliKey(year, month, day));
}

export function getIranHolidaysForMonth(
  year: number,
  month: number,
): IranHoliday[] {
  return IRAN_HOLIDAYS.filter(
    (holiday) => holiday.year === year && holiday.month === month,
  );
}

export function isIranFriday(year: number, month: number, day: number): boolean {
  return JalaliDateTime.fromJalali(year, month, day).getDayOfWeek() === 6;
}

export function isIranHolidayOrFriday(
  year: number,
  month: number,
  day: number,
): boolean {
  return (
    Boolean(getIranHoliday(year, month, day)) ||
    isIranFriday(year, month, day)
  );
}

export function getUpcomingIranHolidays(
  from: JalaliDateTime,
  limit = 6,
): IranHoliday[] {
  const fromKey = jalaliKey(from.year, from.month, from.day);

  return IRAN_HOLIDAYS.filter((holiday) => holiday.key >= fromKey).slice(
    0,
    limit,
  );
}

export function formatHolidayDate(holiday: IranHoliday): string {
  const date = JalaliDateTime.fromJalali(
    holiday.year,
    holiday.month,
    holiday.day,
  );
  return date.format("full");
}
