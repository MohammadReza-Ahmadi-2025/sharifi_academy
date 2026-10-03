import {
  PersianDatePicker,
  type PersianDatePickerProps,
} from '@/Components/Admin/shared/ui/persian-date-picker';

/** @deprecated از `PersianDatePicker` با `includeTime` استفاده کنید. */
export type PersianDateTimePickerProps = Omit<PersianDatePickerProps, 'includeTime'>;

export function PersianDateTimePicker(props: PersianDateTimePickerProps) {
  return <PersianDatePicker {...props} includeTime />;
}
