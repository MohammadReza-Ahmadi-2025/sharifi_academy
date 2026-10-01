"use client";

import * as React from "react";
import ReactSelect, {
  components as selectComponents,
  GroupBase,
  Props as ReactSelectProps,
  DropdownIndicatorProps,
  InputProps,
  OptionProps,
  PlaceholderProps,
  SingleValueProps,
  StylesConfig,
} from "react-select";
import { ChevronDown } from "lucide-react";
import { cn } from "@/lib/utils";

export interface SelectOption {
  label: string;
  value: string;
  isDisabled?: boolean;
}

export interface SelectProps extends Omit<
  ReactSelectProps<SelectOption, false, GroupBase<SelectOption>>,
  "options" | "value" | "onChange"
> {
  label?: string;
  error?: string;
  options: SelectOption[];
  value?: SelectOption | null;
  onChange?: (value: SelectOption | null) => void;
  /** Ctrl/⌘+click an option without replacing the current value. */
  onCtrlSelect?: (option: SelectOption) => void;
  containerClassName?: string;
  /** Show the full selected label with line breaks instead of truncating. */
  wrapSelectedLabel?: boolean;
  /** Stretch the control to the full height of its parent. */
  fillHeight?: boolean;
}

function DropdownIndicator(
  props: DropdownIndicatorProps<SelectOption, false, GroupBase<SelectOption>>,
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

const DEFAULT_MAX_MENU_HEIGHT = 240;

function placeInputCaretAtStart(event: React.FocusEvent<HTMLInputElement>) {
  const input = event.currentTarget;
  requestAnimationFrame(() => {
    input.setSelectionRange(0, 0);
  });
}

function buildFillHeightStyles(
  styles: StylesConfig<SelectOption, false> | undefined,
): StylesConfig<SelectOption, false> {
  return {
    control: (base, state) => ({
      ...(typeof styles?.control === "function"
        ? styles.control(base, state)
        : { ...base, ...(styles?.control as object | undefined) }),
      minHeight: 40,
      height: "100%",
      alignItems: "center",
    }),
  };
}

function buildWrapSelectedLabelStyles(
  styles: StylesConfig<SelectOption, false> | undefined,
  fillHeight = false,
): StylesConfig<SelectOption, false> {
  return {
    control: (base, state) => ({
      ...(typeof styles?.control === "function"
        ? styles.control(base, state)
        : { ...base, ...(styles?.control as object | undefined) }),
      minHeight: 40,
      height: fillHeight ? "100%" : "auto",
      alignItems: "center",
      overflow: "visible",
    }),
    valueContainer: (base, state) => ({
      ...(typeof styles?.valueContainer === "function"
        ? styles.valueContainer(base, state)
        : { ...base, ...(styles?.valueContainer as object | undefined) }),
      position: "relative",
      display: fillHeight ? "flex" : "block",
      height: fillHeight ? "100%" : undefined,
      overflow: "visible",
      alignItems: fillHeight ? "center" : "stretch",
    }),
    singleValue: (base, state) => ({
      ...(typeof styles?.singleValue === "function"
        ? styles.singleValue(base, state)
        : { ...base, ...(styles?.singleValue as object | undefined) }),
      position: "static",
      gridArea: "unset",
      maxWidth: "100%",
      overflow: "visible",
      textOverflow: "clip",
      whiteSpace: "normal",
      wordBreak: "break-word",
      margin: 0,
    }),
    input: (base, state) => {
      const menuOpen = state.selectProps.menuIsOpen;
      const merged = {
        ...(typeof styles?.input === "function"
          ? styles.input(base, state)
          : { ...base, ...(styles?.input as object | undefined) }),
        gridArea: "unset",
        position: "absolute" as const,
        inset: 0,
        width: "100%",
        height: "100%",
        minWidth: "100%",
        margin: 0,
        padding: 0,
        zIndex: 2,
        display: "flex",
        alignItems: "center" as const,
        ...(menuOpen ? {} : { opacity: 0, cursor: "pointer" as const }),
      };

      return merged;
    },
    placeholder: (base, state) => ({
      ...(typeof styles?.placeholder === "function"
        ? styles.placeholder(base, state)
        : { ...base, ...(styles?.placeholder as object | undefined) }),
      position: "relative",
      zIndex: 1,
      gridArea: "unset",
      pointerEvents: "none",
    }),
  };
}

export function Select({
  label,
  error,
  options,
  value,
  onChange,
  onCtrlSelect,
  placeholder = "انتخاب کنید...",
  isSearchable = true,
  isDisabled = false,
  className,
  containerClassName,
  menuPortalTarget,
  menuPosition,
  menuPlacement = "auto",
  maxMenuHeight = DEFAULT_MAX_MENU_HEIGHT,
  wrapSelectedLabel = false,
  fillHeight = false,
  styles,
  components: userComponents,
  ...props
}: SelectProps) {
  const instanceId = React.useId();
  const onCtrlSelectRef = React.useRef(onCtrlSelect);
  onCtrlSelectRef.current = onCtrlSelect;
  const [portalTarget, setPortalTarget] = React.useState<HTMLElement | null>(
    () => (typeof document !== "undefined" ? document.body : null),
  );

  React.useEffect(() => {
    setPortalTarget(document.body);
  }, []);

  const Option = React.useMemo(
    () =>
      function Option(
        optionProps: OptionProps<SelectOption, false, GroupBase<SelectOption>>,
      ) {
        return (
          <selectComponents.Option
            {...optionProps}
            innerProps={{
              ...optionProps.innerProps,
              onClick: (event) => {
                if (
                  onCtrlSelectRef.current &&
                  (event.ctrlKey || event.metaKey) &&
                  !optionProps.isDisabled
                ) {
                  event.preventDefault();
                  event.stopPropagation();
                  onCtrlSelectRef.current(optionProps.data);
                  return;
                }
                optionProps.innerProps.onClick?.(event);
              },
            }}
          />
        );
      },
    [],
  );

  const SearchInput = React.useMemo(
    () =>
      function SearchInput(
        inputProps: InputProps<SelectOption, false, GroupBase<SelectOption>>,
      ) {
        return (
          <selectComponents.Input
            {...inputProps}
            onFocus={(event) => {
              placeInputCaretAtStart(event);
              inputProps.onFocus?.(event);
            }}
          />
        );
      },
    [],
  );

  const WrapSingleValue = React.useMemo(
    () =>
      function WrapSingleValue(
        valueProps: SingleValueProps<
          SelectOption,
          false,
          GroupBase<SelectOption>
        >,
      ) {
        if (valueProps.selectProps.menuIsOpen) {
          return null;
        }

        return (
          <selectComponents.SingleValue
            {...valueProps}
            innerProps={{
              ...valueProps.innerProps,
              className: cn(
                valueProps.innerProps?.className,
                "pointer-events-none relative z-[1] block w-full whitespace-normal break-words leading-snug",
              ),
            }}
          />
        );
      },
    [],
  );

  const WrapPlaceholder = React.useMemo(
    () =>
      function WrapPlaceholder(
        placeholderProps: PlaceholderProps<
          SelectOption,
          false,
          GroupBase<SelectOption>
        >,
      ) {
        return (
          <selectComponents.Placeholder
            {...placeholderProps}
            innerProps={{
              ...placeholderProps.innerProps,
              className: cn(
                placeholderProps.innerProps?.className,
                "pointer-events-none relative z-[1] text-slate-400 dark:text-slate-500",
              ),
            }}
          />
        );
      },
    [],
  );

  const WrapInput = React.useMemo(
    () =>
      function WrapInput(
        inputProps: InputProps<SelectOption, false, GroupBase<SelectOption>>,
      ) {
        const menuOpen = inputProps.selectProps.menuIsOpen;

        return (
          <selectComponents.Input
            {...inputProps}
            isHidden={false}
            readOnly={!menuOpen}
            onFocus={(event) => {
              placeInputCaretAtStart(event);
              inputProps.onFocus?.(event);
            }}
            inputClassName={cn(
              inputProps.inputClassName,
              menuOpen
                ? inputProps.selectProps.inputValue
                  ? "text-slate-900 dark:text-slate-100 caret-current"
                  : "text-slate-400 dark:text-slate-500 caret-current"
                : "cursor-pointer caret-transparent",
            )}
          />
        );
      },
    [],
  );

  const heightStyles = wrapSelectedLabel
    ? buildWrapSelectedLabelStyles(styles, fillHeight)
    : fillHeight
      ? buildFillHeightStyles(styles)
      : undefined;

  return (
    <div
      className={cn(
        "w-full space-y-1.5 text-right",
        fillHeight && !label && "flex h-full flex-col",
        containerClassName,
      )}
      dir="rtl"
    >
      {label && (
        <label className="text-xs font-semibold text-slate-700 dark:text-slate-300">
          {label}
        </label>
      )}

      <ReactSelect<SelectOption, false, GroupBase<SelectOption>>
        instanceId={instanceId}
        inputId={instanceId}
        options={options}
        value={value ?? null}
        onChange={(selected) => onChange?.(selected)}
        onKeyDown={(event) => {
          if (
            event.key === "Enter" &&
            event.target instanceof HTMLInputElement &&
            event.target.getAttribute("role") === "combobox" &&
            event.target.getAttribute("aria-expanded") !== "true"
          ) {
            event.preventDefault();
          }
          props.onKeyDown?.(event);
        }}
        placeholder={placeholder}
        isSearchable={isSearchable}
        isDisabled={isDisabled}
        isRtl
        unstyled
        noOptionsMessage={() => "موردی یافت نشد"}
        classNamePrefix="rs"
        classNames={{
          container: () => cn("w-full", fillHeight && "h-full"),
          control: (state) =>
            cn(
              "w-full min-w-0 rounded-md border bg-white text-sm dark:bg-slate-950",
              "flex min-h-10 items-center",
              fillHeight ? "h-full" : "h-auto",
              wrapSelectedLabel ? "overflow-visible" : "overflow-hidden",
              error
                ? "border-rose-500 dark:border-rose-500"
                : state.isFocused
                  ? "border-slate-400 ring-2 ring-slate-200 dark:border-slate-600 dark:ring-slate-800"
                  : "border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700",
              state.isDisabled && "cursor-not-allowed opacity-60",
              className,
            ),

          valueContainer: () =>
            cn(
              "flex-1 min-w-0 px-3 py-2 text-sm text-slate-900 dark:text-slate-100",
              fillHeight && "h-full",
              wrapSelectedLabel &&
                cn("relative overflow-visible", fillHeight && "flex items-center"),
              !wrapSelectedLabel && "overflow-hidden",
            ),

          placeholder: () =>
            cn(
              "truncate text-sm text-slate-400 dark:text-slate-500",
              wrapSelectedLabel && "pointer-events-none relative z-[1]",
            ),

          singleValue: () =>
            cn(
              "text-sm text-slate-900 dark:text-slate-100",
              wrapSelectedLabel
                ? "pointer-events-none relative z-[1] whitespace-normal break-words leading-snug"
                : "truncate",
            ),

          input: () =>
            "m-0 max-w-full text-sm text-slate-900 dark:text-slate-100",

          indicatorsContainer: () => "flex shrink-0 items-center self-center px-2",

          clearIndicator: () =>
            "cursor-pointer p-1 text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300",

          dropdownIndicator: () =>
            "cursor-pointer p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200",

          menu: () =>
            "z-[9999] my-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900",

          menuList: () =>
            "overflow-y-auto overflow-x-hidden overscroll-contain p-1.5",

          option: (state) =>
            cn(
              "rounded-lg px-3 py-2 text-sm break-words",
              state.isDisabled
                ? "cursor-not-allowed text-slate-400 opacity-50 dark:text-slate-500"
                : "cursor-pointer",
              !state.isDisabled &&
                (state.isSelected
                  ? "bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100"
                  : state.isFocused
                    ? "bg-slate-50 text-slate-900 dark:bg-slate-800/70 dark:text-slate-100"
                    : "text-slate-700 dark:text-slate-200"),
            ),

          noOptionsMessage: () =>
            "px-3 py-2 text-xs text-slate-500 dark:text-slate-400",
        }}
        {...props}
        components={{
          DropdownIndicator,
          IndicatorSeparator: null,
          Option,
          ...(isSearchable ? { Input: SearchInput } : {}),
          ...(wrapSelectedLabel
            ? {
                SingleValue: WrapSingleValue,
                Placeholder: WrapPlaceholder,
                Input: WrapInput,
              }
            : {}),
          ...userComponents,
        }}
        menuPlacement={menuPlacement}
        maxMenuHeight={maxMenuHeight}
        menuShouldScrollIntoView={false}
        menuPortalTarget={menuPortalTarget ?? portalTarget}
        menuPosition={menuPosition ?? "fixed"}
        styles={{
          ...heightStyles,
          ...styles,
          menu: (base, state) => {
            const next =
              typeof styles?.menu === "function"
                ? styles.menu(base, state)
                : { ...base, ...(styles?.menu as object | undefined) };
            return {
              ...next,
              minWidth: base.width ?? 280,
              maxWidth: "min(36rem, calc(100vw - 1.5rem))",
            };
          },
          menuList: (base, state) => {
            const next =
              typeof styles?.menuList === "function"
                ? styles.menuList(base, state)
                : { ...base, ...(styles?.menuList as object | undefined) };
            return {
              ...next,
              maxHeight: maxMenuHeight,
              overflowY: "auto",
              overflowX: "hidden",
            };
          },
          menuPortal: (base, state) => ({
            ...(typeof styles?.menuPortal === "function"
              ? styles.menuPortal(base, state)
              : base),
            zIndex: 9999,
          }),
        }}
      />

      {error && (
        <p className="text-xs text-rose-500 dark:text-rose-400">{error}</p>
      )}
    </div>
  );
}
