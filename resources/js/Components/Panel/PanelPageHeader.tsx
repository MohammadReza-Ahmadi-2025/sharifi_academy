import { Link } from '@inertiajs/react';
import { ArrowUpLeft, LayoutDashboard, Menu } from 'lucide-react';

type Props = {
    eyebrow: string;
    title: string;
    description?: string;
    navId: string;
    mobileNavOpen: boolean;
    onToggleNav: () => void;
    /** لینک بازگشت به داشبورد؛ اگر null باشد دکمه نشان داده نمی‌شود */
    dashboardHref?: string | null;
    dashboardLabel?: string;
};

export function PanelPageHeader({
    eyebrow,
    title,
    description,
    navId,
    mobileNavOpen,
    onToggleNav,
    dashboardHref = null,
    dashboardLabel = 'داشبورد',
}: Props) {
    return (
        <header className="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur-md supports-[backdrop-filter]:bg-white/85">
            {/* Mobile: single compact toolbar */}
            <div className="flex h-14 items-center gap-2.5 px-3 sm:px-4 lg:hidden">
                <button
                    type="button"
                    className="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-800 transition active:scale-95"
                    aria-label="باز کردن منو"
                    aria-expanded={mobileNavOpen}
                    aria-controls={navId}
                    onClick={onToggleNav}
                >
                    <Menu className="size-5" aria-hidden />
                </button>

                <div className="min-w-0 flex-1">
                    <p className="truncate text-[10px] font-medium text-slate-500">{eyebrow}</p>
                    <h1 className="truncate text-[15px] font-bold leading-tight text-slate-900">{title}</h1>
                </div>

                {dashboardHref ? (
                    <Link
                        href={dashboardHref}
                        className="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition active:scale-95"
                        aria-label={dashboardLabel}
                        title={dashboardLabel}
                    >
                        <LayoutDashboard className="size-[1.125rem]" aria-hidden />
                    </Link>
                ) : (
                    <span className="size-10 shrink-0" aria-hidden />
                )}
            </div>

            {/* Desktop */}
            <div className="hidden px-8 py-5 lg:block">
                <div className="flex items-end justify-between gap-4">
                    <div className="min-w-0">
                        <p className="text-[11px] font-semibold tracking-[0.16em] text-primary/80 uppercase">
                            {eyebrow}
                        </p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
                        {description ? (
                            <p className="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-600">{description}</p>
                        ) : null}
                    </div>
                    {dashboardHref ? (
                        <Link
                            href={dashboardHref}
                            className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-primary/30 hover:text-primary"
                        >
                            <ArrowUpLeft className="size-3.5" aria-hidden />
                            {dashboardLabel}
                        </Link>
                    ) : null}
                </div>
            </div>
        </header>
    );
}
