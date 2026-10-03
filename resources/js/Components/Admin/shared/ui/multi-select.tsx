"use client";

import * as React from "react";
import ReactSelect, {
  components as selectComponents,
  GroupBase,
  Props as ReactSelectProps,
  DropdownIndicatorProps,
} from "react-select";
import { ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";
import type { SelectOption } from "@/Components/Admin/shared/ui/select";

export interface MultiSelectProps extends Omit<
  ReactSelectProps<SelectOption, true, GroupBase<SelectOption>>,
  "options" | "value" | "onChange" | "isMulti"
> {
  label?: string;
  error?: string;
  options: SelectOption[];
  value?: SelectOption[];
  onChange?: (value: SelectOption[]) => void;
  containerClassName?: string;
}

function DropdownIndicator(
  props: DropdownIndicatorProps<SelectOption, true, GroupBase<SelectOption>>,
) {
  return (
    <selectComponents.DropdownIndicator {...props}>
      <ChevronDown
        className={cn(
          "h-4 w-4 text-slate-500 transition-transform duration-200 dark:text-slate-400",
          props.selectProps.menuIsOpen && "rotate-180",
        )}
      />
    </selectComponents.DropdownIndicator>
  );
}

export function MultiSelect({
  label,
  error,
  options,
  value = [],
  onChange,
  placeholder = "انتخاب کنید...",
  isSearchable = true,
  isDisabled = false,
  className,
  containerClassName,
  ...props
}: MultiSelectProps) {
  const instanceId = React.useId();
  const [portalTarget, setPortalTarget] = React.useState<HTMLElement | null>(null);

  React.useEffect(() => {
    setPortalTarget(document.body);
  }, []);

  return (
    <div className={cn("w-full space-y-1.5 text-right", containerClassName)} dir="rtl">
      {label && (
        <label className="text-xs font-semibold text-slate-700 dark:text-slate-300">{label}</label>
      )}

      <ReactSelect<SelectOption, true, GroupBase<SelectOption>>
        instanceId={instanceId}
        inputId={instanceId}
        isMulti
        options={options}
        value={value}
        onChange={(selected) => onChange?.([...(selected ?? [])])}
        placeholder={placeholder}
        isSearchable={isSearchable}
        isDisabled={isDisabled}
        isRtl
        unstyled
        closeMenuOnSelect={false}
        noOptionsMessage={() => "موردی یافت نشد"}
        classNamePrefix="rs"
        classNames={{
          container: () => "w-full",
          control: (state) =>
            cn(
              "w-full min-w-0 rounded-md border bg-white text-sm dark:bg-slate-950",
              "flex min-h-10 items-center",
              error
                ? "border-rose-500 dark:border-rose-500"
                : state.isFocused
                  ? "border-slate-400 ring-2 ring-slate-200 dark:border-slate-600 dark:ring-slate-800"
                  : "border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700",
              state.isDisabled && "cursor-not-allowed opacity-60",
              className,
            ),
          valueContainer: () =>
            "flex flex-1 flex-wrap gap-1 px-2 py-1.5 text-sm text-slate-900 dark:text-slate-100",
          placeholder: () => "text-sm text-slate-400 dark:text-slate-500",
          multiValue: () =>
            "rounded-md bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100",
          multiValueLabel: () => "px-2 py-0.5 text-xs",
          multiValueRemove: () =>
            "rounded-e-md px-1 text-slate-500 hover:bg-slate-200 hover:text-slate-800 dark:hover:bg-slate-700",
          input: () => "m-0 text-sm text-slate-900 dark:text-slate-100",
          indicatorsContainer: () => "flex shrink-0 items-center self-center px-2",
          dropdownIndicator: () =>
            "cursor-pointer p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400",
          clearIndicator: () =>
            "cursor-pointer p-1 text-slate-400 hover:text-slate-600 dark:text-slate-500",
          menu: () =>
            "z-[9999] my-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900",
          menuList: () => "max-h-60 overflow-y-auto p-1.5",
          option: (state) =>
            cn(
              "rounded-lg px-3 py-2 text-sm cursor-pointer",
              state.isSelected
                ? "bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100"
                : state.isFocused
                  ? "bg-slate-50 dark:bg-slate-800/70"
                  : "text-slate-700 dark:text-slate-200",
            ),
          noOptionsMessage: () => "px-3 py-2 text-xs text-slate-500",
        }}
        components={{ DropdownIndicator, IndicatorSeparator: null }}
        menuPortalTarget={portalTarget}
        menuPosition="fixed"
        styles={{
          menuPortal: (base) => ({ ...base, zIndex: 9999 }),
        }}
        {...props}
      />

      {error && <p className="text-xs text-rose-500 dark:text-rose-400">{error}</p>}
    </div>
  );
}
