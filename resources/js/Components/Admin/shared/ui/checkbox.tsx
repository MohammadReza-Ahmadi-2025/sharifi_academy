import * as React from "react";
import { cn } from "@/lib/utils";

export interface CheckboxProps extends Omit<
  React.InputHTMLAttributes<HTMLInputElement>,
  "type"
> {
  label?: string;
  description?: string;
  containerClassName?: string;
}

export const Checkbox = React.forwardRef<HTMLInputElement, CheckboxProps>(
  (
    {
      className,
      label,
      description,
      containerClassName,
      id,
      disabled,
      ...props
    },
    ref,
  ) => {
    const generatedId = React.useId();
    const inputId = id ?? generatedId;

    return (
      <div className={cn("flex items-start gap-3", containerClassName)}>
        <input
          ref={ref}
          id={inputId}
          type="checkbox"
          disabled={disabled}
          className={cn(
            "mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 accent-slate-900",
            "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2",
            "disabled:cursor-not-allowed disabled:opacity-50",
            "dark:border-slate-700 dark:accent-slate-200 dark:focus-visible:ring-slate-600 dark:focus-visible:ring-offset-slate-950",
            className,
          )}
          {...props}
        />

        {(label || description) && (
          <div className="min-w-0 space-y-0.5">
            {label && (
              <label
                htmlFor={inputId}
                className={cn(
                  "block text-sm font-medium text-slate-700 dark:text-slate-300",
                  disabled ? "cursor-not-allowed opacity-50" : "cursor-pointer",
                )}
              >
                {label}
              </label>
            )}

            {description && (
              <p className="text-xs text-slate-500 dark:text-slate-400">
                {description}
              </p>
            )}
          </div>
        )}
      </div>
    );
  },
);

Checkbox.displayName = "Checkbox";
