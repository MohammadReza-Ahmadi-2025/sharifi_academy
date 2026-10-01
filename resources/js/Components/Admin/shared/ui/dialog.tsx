import * as React from "react";
import { cn } from "@/lib/utils";

interface DialogProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
  panelClassName?: string;
  contentClassName?: string;
  /** Hide the default title bar (useful for fully custom layouts). */
  hideHeader?: boolean;
  /** Fill the viewport (edge-to-edge panel). */
  fullScreen?: boolean;
}

export function Dialog({
  isOpen,
  onClose,
  title = "",
  children,
  panelClassName,
  contentClassName,
  hideHeader = false,
  fullScreen = false,
}: DialogProps) {
  React.useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };

    if (isOpen) {
      document.body.style.overflow = "hidden";
      window.addEventListener("keydown", handleEscape);
    }

    return () => {
      document.body.style.overflow = "unset";
      window.removeEventListener("keydown", handleEscape);
    };
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div
      className={cn(
        "fixed inset-0 z-50 flex bg-slate-900/60 backdrop-blur-sm dark:bg-slate-950/80",
        fullScreen
          ? "p-0"
          : "items-center justify-center p-4",
      )}
      onClick={onClose}
      role="presentation"
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-labelledby={hideHeader || !title ? undefined : "dialog-title"}
        className={cn(
          "flex w-full flex-col overflow-hidden border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900",
          fullScreen
            ? "h-full max-h-none max-w-none rounded-none"
            : "max-h-[90vh] max-w-lg animate-in fade-in-50 zoom-in-95 rounded-xl duration-200",
          panelClassName,
        )}
        dir="rtl"
        onClick={(event) => event.stopPropagation()}
      >
        {!hideHeader && (
          <div className="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
            <h3
              id="dialog-title"
              className="text-base font-semibold text-slate-900 dark:text-slate-100"
            >
              {title}
            </h3>
            <button
              type="button"
              onClick={onClose}
              aria-label="بستن"
              className="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
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
          </div>
        )}
        <div
          className={cn(
            "min-h-0 flex-1",
            fullScreen
              ? "flex flex-col overflow-hidden"
              : "overflow-y-auto overscroll-contain",
            !hideHeader && !fullScreen && "px-6 py-4",
            contentClassName,
          )}
        >
          {children}
        </div>
      </div>
    </div>
  );
}
