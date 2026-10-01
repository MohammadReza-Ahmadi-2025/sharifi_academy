"use client";

import * as React from "react";
import { createPortal } from "react-dom";
import { X } from "lucide-react";
import { cn } from "@/lib/utils";

type PopoverPanelAlign = "start" | "end" | "center";
type PopoverPanelPlacement = "top" | "bottom" | "auto";

interface PopoverPanelProps {
  trigger: React.ReactNode;
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  title?: string;
  children: React.ReactNode;
  className?: string;
  panelClassName?: string;
  align?: PopoverPanelAlign;
  placement?: PopoverPanelPlacement;
  /** On viewports below `md`, cover the whole screen instead of anchoring. */
  fullScreenOnMobile?: boolean;
}

const DEFAULT_PANEL_WIDTH = 352;
const DEFAULT_PANEL_HEIGHT = 420;
const MOBILE_MEDIA_QUERY = "(max-width: 767px)";

function clamp(value: number, min: number, max: number) {
  return Math.min(Math.max(value, min), max);
}

function computePanelPosition(
  rect: DOMRect,
  align: PopoverPanelAlign,
  panelWidth: number,
  panelHeight: number,
  placement: PopoverPanelPlacement,
) {
  const gap = 10;
  let left: number;

  if (align === "center") {
    left = rect.left + rect.width / 2 - panelWidth / 2;
  } else if (align === "start") {
    left = rect.right - panelWidth;
  } else {
    left = rect.left;
  }

  left = clamp(left, 8, window.innerWidth - panelWidth - 8);

  const spaceBelow = window.innerHeight - rect.bottom - gap;
  const spaceAbove = rect.top - gap;

  let effectivePlacement = placement;
  if (placement === "auto") {
    effectivePlacement =
      spaceBelow >= panelHeight || spaceBelow >= spaceAbove ? "bottom" : "top";
  }

  let top =
    effectivePlacement === "top"
      ? rect.top - panelHeight - gap
      : rect.bottom + gap;

  top = clamp(top, 8, window.innerHeight - panelHeight - 8);

  return { top, left, opensUp: effectivePlacement === "top" };
}

function useIsMobile() {
  const [isMobile, setIsMobile] = React.useState(false);

  React.useEffect(() => {
    const media = window.matchMedia(MOBILE_MEDIA_QUERY);
    const sync = () => setIsMobile(media.matches);
    sync();
    media.addEventListener("change", sync);
    return () => media.removeEventListener("change", sync);
  }, []);

  return isMobile;
}

export function PopoverPanel({
  trigger,
  isOpen,
  onOpenChange,
  title,
  children,
  className,
  panelClassName,
  align = "end",
  placement = "bottom",
  fullScreenOnMobile = false,
}: PopoverPanelProps) {
  const triggerRef = React.useRef<HTMLDivElement>(null);
  const panelRef = React.useRef<HTMLDivElement>(null);
  const [mounted, setMounted] = React.useState(false);
  const [position, setPosition] = React.useState<{
    top: number;
    left: number;
    opensUp: boolean;
  } | null>(null);
  const isMobile = useIsMobile();
  const isFullScreen = fullScreenOnMobile && isMobile;

  React.useEffect(() => {
    setMounted(true);
  }, []);

  const updatePosition = React.useCallback(() => {
    if (isFullScreen) return;

    const triggerEl = triggerRef.current;
    const panelEl = panelRef.current;
    if (!triggerEl) return;

    const rect = triggerEl.getBoundingClientRect();
    const panelWidth =
      panelEl?.offsetWidth ||
      Math.min(DEFAULT_PANEL_WIDTH, window.innerWidth - 32);
    const panelHeight = panelEl?.offsetHeight || DEFAULT_PANEL_HEIGHT;

    setPosition(
      computePanelPosition(rect, align, panelWidth, panelHeight, placement),
    );
  }, [align, isFullScreen, placement]);

  React.useEffect(() => {
    if (!isOpen || isFullScreen) {
      setPosition(null);
      return;
    }

    updatePosition();

    const handleReposition = () => updatePosition();
    window.addEventListener("resize", handleReposition);
    window.addEventListener("scroll", handleReposition, true);

    return () => {
      window.removeEventListener("resize", handleReposition);
      window.removeEventListener("scroll", handleReposition, true);
    };
  }, [isOpen, isFullScreen, updatePosition]);

  React.useEffect(() => {
    if (!isOpen || isFullScreen) return;

    requestAnimationFrame(() => updatePosition());
  }, [isOpen, isFullScreen, updatePosition, children]);

  React.useEffect(() => {
    if (!isOpen) return;

    const handleClickOutside = (event: MouseEvent) => {
      if (isFullScreen) return;

      const target = event.target as Node;
      if (
        triggerRef.current?.contains(target) ||
        panelRef.current?.contains(target)
      ) {
        return;
      }
      onOpenChange(false);
    };

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key !== "Escape") return;
      event.preventDefault();
      event.stopPropagation();
      onOpenChange(false);
    };

    document.addEventListener("mousedown", handleClickOutside);
    window.addEventListener("keydown", handleEscape, true);

    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      window.removeEventListener("keydown", handleEscape, true);
    };
  }, [isOpen, isFullScreen, onOpenChange]);

  const panel = isOpen ? (
    isFullScreen ? (
      <div
        className="fixed inset-0 z-[10000] flex flex-col bg-slate-950/50 backdrop-blur-sm"
        role="presentation"
        onClick={() => onOpenChange(false)}
      >
        <div
          ref={panelRef}
          role="dialog"
          aria-modal="true"
          aria-label={title}
          dir="rtl"
          onClick={(event) => event.stopPropagation()}
          className={cn(
            "flex h-full min-h-0 w-full flex-col overflow-hidden bg-white dark:bg-slate-900",
            "animate-in fade-in-0 zoom-in-95 duration-200",
          )}
        >
          <div className="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">
              {title ?? "انتخاب"}
            </h3>
            <button
              type="button"
              onClick={() => onOpenChange(false)}
              aria-label="بستن"
              className="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
          <div className="min-h-0 flex-1 overflow-y-auto overscroll-contain">
            {children}
          </div>
        </div>
      </div>
    ) : (
      <div
        ref={panelRef}
        role="dialog"
        aria-modal="false"
        aria-label={title}
        style={
          position
            ? {
                position: "fixed",
                top: position.top,
                left: position.left,
              }
            : {
                position: "fixed",
                top: "calc(4rem + 10px)",
                left: "50%",
                transform: "translateX(-50%)",
              }
        }
        className={cn(
          "z-[10000] w-[min(calc(100vw-2rem),22rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10",
          position?.opensUp
            ? "animate-in fade-in-0 zoom-in-95 slide-in-from-bottom-2 duration-200"
            : "animate-in fade-in-0 zoom-in-95 slide-in-from-top-2 duration-200",
          "dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30",
          panelClassName,
        )}
      >
        {title && !fullScreenOnMobile && (
          <div className="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <h3 className="text-sm font-semibold text-slate-900 dark:text-slate-100">
              {title}
            </h3>
          </div>
        )}

        {children}
      </div>
    )
  ) : null;

  return (
    <div className={cn("relative", className)}>
      <div ref={triggerRef} onClick={() => onOpenChange(!isOpen)}>
        {trigger}
      </div>

      {mounted && panel ? createPortal(panel, document.body) : null}
    </div>
  );
}
