import { HeroVisualCarousel } from '@/Components/Home/HeroVisualCarousel';
import { Link } from '@inertiajs/react';
import { ArrowLeft, BookOpen, MonitorPlay, ShieldCheck, Sparkles } from 'lucide-react';

const highlights = [
    { icon: MonitorPlay, label: 'کلاس زنده Adobe Connect' },
    { icon: BookOpen, label: 'کتاب تخصصی PDF' },
    { icon: ShieldCheck, label: 'پرداخت امن زرین‌پال' },
];

export function HomeHeroSection() {
    return (
        <section className="relative overflow-hidden border-b border-secondary bg-neutral-white">
            <div
                className="pointer-events-none absolute -top-24 left-1/2 h-72 w-[120%] -translate-x-1/2 rounded-[100%] bg-primary/10 blur-3xl"
                aria-hidden
            />
            <div
                className="pointer-events-none absolute inset-0 opacity-[0.35]"
                style={{
                    backgroundImage:
                        'linear-gradient(to left, rgb(var(--color-secondary-rgb) / 0.5) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--color-secondary-rgb) / 0.5) 1px, transparent 1px)',
                    backgroundSize: '48px 48px',
                }}
                aria-hidden
            />

            <div className="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-12 md:py-16 lg:grid-cols-2 lg:gap-16 lg:py-20">
                <div className="order-2 lg:order-1">
                    <span className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-semibold text-primary">
                        <Sparkles className="size-3.5" />
                        آکادمی تخصصی مهندسی
                    </span>

                    <h1 className="mt-5 text-3xl font-extrabold leading-[1.15] tracking-tight text-neutral-black md:text-4xl lg:text-[2.75rem]">
                        مهارت مهندسی را
                        <span className="relative mx-1 inline-block text-primary">
                            عملی
                            <span className="absolute -bottom-1 right-0 left-0 h-1 rounded-full bg-secondary" aria-hidden />
                        </span>
                        یاد بگیرید
                    </h1>

                    <p className="mt-5 max-w-md text-base leading-relaxed text-neutral-black/70 md:text-lg">
                        دوره‌های کلاس‌محور با استاد، منابع کتابی مکمل، و پنل دانشجو برای ورود سریع به هر جلسه — همه در یک
                        مسیر آموزشی منسجم.
                    </p>

                    <ul className="mt-6 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                        {highlights.map(({ icon: Icon, label }) => (
                            <li
                                key={label}
                                className="inline-flex items-center gap-2 rounded-xl bg-surface-muted px-3 py-2 text-sm font-medium text-neutral-black/80"
                            >
                                <span className="flex size-8 items-center justify-center rounded-lg bg-primary text-on-primary">
                                    <Icon className="size-4" />
                                </span>
                                {label}
                            </li>
                        ))}
                    </ul>

                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <a
                            href="#courses"
                            className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-on-primary shadow-md shadow-primary/25 transition hover:bg-primary-hover"
                        >
                            مشاهده دوره‌ها
                            <ArrowLeft className="size-4" />
                        </a>
                            <Link
                                href="/login"
                                className="inline-flex items-center rounded-xl border-2 border-primary/15 bg-neutral-white px-6 py-3 text-sm font-semibold text-primary transition hover:border-primary/30 hover:bg-primary/5"
                            >
                                ورود / ثبت‌نام
                            </Link>
                    </div>
                </div>

                <div className="order-1 pb-6 lg:order-2 lg:pb-0">
                    <div className="relative">
                        <HeroVisualCarousel />

                        <a
                            href="#books"
                            className="hero-float-delayed absolute -bottom-6 -left-2 z-40 hidden md:flex max-w-[min(100%,14rem)] items-center gap-3 rounded-2xl border border-secondary bg-neutral-white p-2 shadow-lg transition hover:shadow-xl"
                        >
                            <img src="/banner.png" alt="" className="size-16 shrink-0 rounded-xl object-cover" />
                            <div className="min-w-0 pe-2">
                                <p className="text-xs font-bold text-primary">کتاب‌های آکادمی</p>
                                <p className="text-[11px] leading-snug text-neutral-black/60">منابع مکتوب هم‌مسیر با کلاس</p>
                            </div>
                        </a>

                        <div
                            className="hero-float absolute -top-4 -left-4 z-40 hidden rounded-2xl border border-secondary bg-neutral-white px-4 py-3 shadow-md md:block"
                        >
                            <p className="text-2xl font-extrabold text-primary">Live</p>
                            <p className="text-xs text-neutral-black/60">جلسه آنلاین</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
