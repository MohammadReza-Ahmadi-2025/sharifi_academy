import { cn } from "@/lib/utils";
import {
  formatPersianDateTime,
  persianDateTimeClassName,
  type PersianDateTimeStyle,
} from "@/Components/Admin/shared/lib/format-persian-datetime";

type PersianDateTimeTextProps = {
  value: string | Date | null | undefined;
  style?: PersianDateTimeStyle;
  className?: string;
};

export function PersianDateTimeText({
  value,
  style = "table",
  className,
}: PersianDateTimeTextProps) {
  return (
    <span className={cn(persianDateTimeClassName, className)}>
      {formatPersianDateTime(value, style)}
    </span>
  );
}
