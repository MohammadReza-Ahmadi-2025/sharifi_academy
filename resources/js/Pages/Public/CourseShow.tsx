import {
    CourseCommentsSection,
    type PublicCourseComment,
} from '@/Components/Courses/CourseCommentsSection';
import { SiteCatalogSeoHead } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import AppLayout from '@/Layouts/AppLayout';
import { siteSettingsFromPage } from '@/lib/site-settings';
import type { PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import {
    ArrowLeft,
    BookOpen,
    CalendarDays,
    ChevronLeft,
    Clock,
    GraduationCap,
    Layers,
    MapPin,
    MessageSquare,
    Phone,
    Sparkles,
} from 'lucide-react';

type CourseClassRow = {
    id: number;
    name: string;
    delivery_type_label: string;
    online_platform_label?: string | null;
    price: number | null;
    location: string | null;
    schedule_status: 'unscheduled' | 'upcoming' | 'ongoing' | 'finished';
    schedule_status_label: string;
    schedule_range_label: string | null;
    starts_at: string | null;
    ends_at: string | null;
};

type Props = {
    course: {
        id: number;
        title: string;
        slug: string;
        short_description: string | null;
        description: string | null;
        level_label: string | null;
        duration_label: string | null;
        image_url: string | null;
        gallery: { id: number; url: string; alt: string }[];
        price_from: number | null;
        categories: { id: number; title: string; slug: string }[];
        classes: CourseClassRow[];
        instructors: { id: number; name: string }[];
        seo: {
            title: string;
            description: string | null;
            keywords: string | null;
            canonical: string;
            og_image: string | null;
            og_type?: string;
        };
        comments: PublicCourseComment[];
        comments_summary: { count: number; average_rating: number | null };
        enrollment: {
            authenticated: boolean;
            profile_complete: boolean;
            profile_url: string;
            login_url: string | null;
            state: 'guest' | 'none' | 'pending' | 'confirmed';
            course_class_id: number | null;
            student_dashboard_url: string | null;
            requestable_class_ids: number[];
        };
    };
    structuredData: {
        breadcrumb: Record<string, unknown>;
        course: Record<string, unknown>;
        webPage: Record<string, unknown>;
    };
};

function formatPrice(amount: number | null) {
    if (amount === null || amount <= 0) {
        return null;
    }

    return `${amount.toLocaleString('fa-IR')} ریال`;
}

function SectionTitle({ icon: Icon, children }: { icon: typeof BookOpen; children: ReactNode }) {
    return (
        <div className="mb-5 flex items-center gap-3">
            <span className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Icon className="size-5" />
            </span>
            <h2 className="text-lg font-extrabold text-neutral-black sm:text-xl">{children}</h2>
            <span className="mr-auto hidden h-px flex-1 bg-gradient-to-l from-secondary to-transparent sm:block" />
        </div>
    );
}

function scheduleBadgeClass(status: CourseClassRow['schedule_status']): string {
    switch (status) {
        case 'ongoing':
            return 'bg-emerald-500/15 text-emerald-800';
        case 'upcoming':
            return 'bg-primary/12 text-primary';
        case 'finished':
            return 'bg-neutral-black/8 text-neutral-black/55';
        default:
            return 'bg-surface-muted text-neutral-black/60';
    }
}

export default function CourseShow({ course, structuredData }: Props) {
    const pageProps = usePage<PageProps>().props;
    const enrollment = course.enrollment;
    const requestableIds = enrollment.requestable_class_ids;
    const defaultClassId =
        requestableIds.length === 1
            ? requestableIds[0]
            : course.classes.find((c) => requestableIds.includes(c.id))?.id ?? requestableIds[0] ?? '';

    const enrollForm = useForm({
        course_class_id: defaultClassId === '' ? '' : String(defaultClassId),
    });

    const settings = siteSettingsFromPage(pageProps);
    const phone = settings.contact_phone?.trim() ?? '';
    const email = settings.contact_email?.trim() ?? '';

    const contactHref = phone ? `tel:${phone.replace(/\s/g, '')}` : email ? `mailto:${email}` : null;
    const contactCta = phone ? 'تماس برای ثبت‌نام' : email ? 'ارسال ایمیل' : 'تماس با آموزش';

    const primaryCategory = course.categories[0];
    const bodyHtml = course.description?.trim() || null;
    const introText = !bodyHtml ? course.short_description?.trim() || null : null;

    return (
        <AppLayout fullWidth>
            <SiteDefaultHead title={course.seo.title} description={course.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={course.seo}
                structuredData={[structuredData.breadcrumb, structuredData.course, structuredData.webPage]}
            />

            <section className="relative overflow-hidden border-b border-secondary bg-neutral-white">
                <div
                    className="pointer-events-none absolute inset-0 opacity-[0.28]"
                    style={{
                        backgroundImage:
                            'linear-gradient(to left, rgb(var(--color-secondary-rgb) / 0.45) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--color-secondary-rgb) / 0.45) 1px, transparent 1px)',
                        backgroundSize: '44px 44px',
                    }}
                    aria-hidden
                />
                <div className="pointer-events-none absolute -top-20 right-0 h-64 w-64 rounded-full bg-primary/15 blur-3xl" aria-hidden />

                <div className="relative mx-auto max-w-[88rem] px-4 pb-10 pt-6 sm:px-6 lg:px-8 lg:pb-14 lg:pt-8">
                    <nav className="mb-6 flex flex-wrap items-center gap-2 text-sm text-neutral-black/55" aria-label="مسیر">
                        <Link href="/" className="transition hover:text-primary">خانه</Link>
                        <span className="text-neutral-black/30">/</span>
                        {primaryCategory ? (
                            <>
                                <Link href={`/categories/${primaryCategory.slug}`} className="transition hover:text-primary">
                                    {primaryCategory.title}
                                </Link>
                                <span className="text-neutral-black/30">/</span>
                            </>
                        ) : null}
                        <span className="font-medium text-neutral-black/80">{course.title}</span>
                    </nav>

                    <div className="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
                        <div className="order-2 lg:order-1">
                            <div className="mb-4 flex flex-wrap gap-2">
                                <span className="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/8 px-3 py-1 text-xs font-bold text-primary">
                                    <Sparkles className="size-3.5" />
                                    دوره آموزشی
                                </span>
                                {course.categories.map((category) => (
                                    <Link
                                        key={category.id}
                                        href={`/categories/${category.slug}`}
                                        className="rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold text-neutral-black/75 transition hover:bg-primary/10 hover:text-primary"
                                    >
                                        {category.title}
                                    </Link>
                                ))}
                                {course.level_label && (
                                    <span className="rounded-full bg-secondary/30 px-3 py-1 text-xs font-semibold text-neutral-black/80">
                                        {course.level_label}
                                    </span>
                                )}
                            </div>

                            <h1 className="text-3xl font-extrabold leading-tight text-neutral-black sm:text-4xl">
                                {course.title}
                            </h1>

                            {course.short_description && (
                                <p className="mt-4 text-base leading-relaxed text-neutral-black/75 sm:text-lg">
                                    {course.short_description}
                                </p>
                            )}

                            <ul className="mt-6 flex flex-wrap gap-3">
                                {course.duration_label && (
                                    <li className="flex items-center gap-2 rounded-xl border border-secondary bg-neutral-white/80 px-3 py-2 text-sm shadow-sm backdrop-blur-sm">
                                        <Clock className="size-4 text-primary" />
                                        {course.duration_label}
                                    </li>
                                )}
                                {course.instructors.length > 0 && (
                                    <li className="flex items-center gap-2 rounded-xl border border-secondary bg-neutral-white/80 px-3 py-2 text-sm shadow-sm backdrop-blur-sm">
                                        <GraduationCap className="size-4 text-primary" />
                                        {course.instructors.map((i) => i.name).join('، ')}
                                    </li>
                                )}
                            </ul>
                        </div>

                        <div className="order-1 lg:order-2">
                            <div className="relative mx-auto max-w-md lg:max-w-none">
                                <div className="absolute -inset-2 rounded-2xl bg-gradient-to-br from-primary/30 to-secondary/40 blur-sm" aria-hidden />
                                <div className="relative overflow-hidden rounded-2xl border-4 border-neutral-white shadow-2xl">
                                    <img
                                        src={course.image_url?.trim() || '/banner.png'}
                                        alt={course.title}
                                        className="aspect-[4/3] w-full object-cover"
                                        fetchPriority="high"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div className="mx-auto w-[88rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                <div className="grid gap-10 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start lg:gap-12">
                    <div className="min-w-0 space-y-12">
                        {bodyHtml && (
                            <section>
                                <SectionTitle icon={BookOpen}>شرح دوره</SectionTitle>
                                <div className="rounded-2xl border border-secondary bg-neutral-white p-6 shadow-sm sm:p-8">
                                    <SiteRichHtml html={bodyHtml} />
                                </div>
                            </section>
                        )}

                        {!bodyHtml && introText && (
                            <section>
                                <SectionTitle icon={BookOpen}>معرفی دوره</SectionTitle>
                                <div className="rounded-2xl border border-secondary bg-neutral-white p-6 text-base leading-relaxed text-neutral-black/80 shadow-sm sm:p-8">
                                    {introText}
                                </div>
                            </section>
                        )}

                        {course.gallery.length > 1 && (
                            <section>
                                <SectionTitle icon={Layers}>تصاویر دوره</SectionTitle>
                                <div className="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory">
                                    {course.gallery.map((image) => (
                                        <img
                                            key={image.id}
                                            src={image.url}
                                            alt={image.alt}
                                            loading="lazy"
                                            decoding="async"
                                            className="aspect-[4/3] w-56 shrink-0 snap-start rounded-xl border border-secondary object-cover shadow-md sm:w-64"
                                        />
                                    ))}
                                </div>
                            </section>
                        )}

                        {course.classes.length > 0 && (
                            <section>
                                <SectionTitle icon={CalendarDays}>زمان‌بندی کلاس‌ها</SectionTitle>
                                <div className="space-y-4">
                                    {course.classes.map((row, index) => (
                                        <article
                                            key={row.id}
                                            className="relative overflow-hidden rounded-2xl border border-secondary bg-neutral-white p-5 shadow-sm transition hover:border-primary/30 hover:shadow-md sm:p-6"
                                        >
                                            <span className="absolute inset-y-0 right-0 w-1 bg-primary" aria-hidden />
                                            <div className="flex flex-wrap items-start justify-between gap-4 pr-3">
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <p className="text-xs font-bold text-primary/80">
                                                            کلاس {(index + 1).toLocaleString('fa-IR')}
                                                        </p>
                                                        <span
                                                            className={`rounded-full px-2.5 py-0.5 text-[11px] font-bold ${scheduleBadgeClass(row.schedule_status)}`}
                                                        >
                                                            {row.schedule_status_label}
                                                        </span>
                                                    </div>
                                                    <h3 className="mt-1 text-lg font-extrabold text-neutral-black">
                                                        {row.name}
                                                    </h3>
                                                    <p className="mt-1 text-sm text-neutral-black/65">
                                                        {row.delivery_type_label}
                                                        {row.online_platform_label
                                                            ? ` · ${row.online_platform_label}`
                                                            : ''}
                                                    </p>
                                                </div>
                                                {row.price !== null && row.price > 0 && (
                                                    <p className="shrink-0 rounded-xl bg-primary/8 px-3 py-2 text-sm font-extrabold text-primary">
                                                        {formatPrice(row.price)}
                                                    </p>
                                                )}
                                            </div>
                                            <ul className="mt-4 space-y-2.5 pr-3 text-sm text-neutral-black/75">
                                                {row.schedule_range_label && (
                                                    <li className="flex items-start gap-2">
                                                        <CalendarDays className="mt-0.5 size-4 shrink-0 text-primary" />
                                                        <span>{row.schedule_range_label}</span>
                                                    </li>
                                                )}
                                                {row.location && (
                                                    <li className="flex items-start gap-2">
                                                        <MapPin className="mt-0.5 size-4 shrink-0 text-primary" />
                                                        <span>{row.location}</span>
                                                    </li>
                                                )}
                                            </ul>
                                        </article>
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>

                    <aside className="lg:sticky lg:top-24">
                        <div className="overflow-hidden rounded-2xl bg-primary text-on-primary shadow-xl shadow-primary/25">
                            <div className="border-b border-white/15 px-5 py-4">
                                <p className="text-sm opacity-90">شهریه از</p>
                                <p className="mt-1 text-2xl font-extrabold tracking-tight">
                                    {course.price_from ? formatPrice(course.price_from) : 'استعلام قیمت'}
                                </p>
                            </div>
                            <div className="space-y-4 px-5 py-5">
                                {enrollment.state === 'guest' && (
                                    <p className="text-sm leading-relaxed opacity-90">
                                        برای درخواست ثبت‌نام ابتدا وارد حساب کاربری شوید.
                                    </p>
                                )}
                                {enrollment.state === 'guest' && enrollment.login_url && (
                                    <Link
                                        href={enrollment.login_url}
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-white px-4 py-3 text-sm font-extrabold text-primary shadow-md transition hover:bg-secondary hover:text-neutral-black"
                                    >
                                        ورود / ثبت‌نام
                                    </Link>
                                )}
                                {enrollment.authenticated && !enrollment.profile_complete && (
                                    <>
                                        <p className="text-sm leading-relaxed opacity-90">
                                            برای ثبت‌نام، نام، نام خانوادگی و شهر را در پروفایل تکمیل کنید.
                                        </p>
                                        <Link
                                            href={enrollment.profile_url}
                                            className="flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-white px-4 py-3 text-sm font-extrabold text-primary shadow-md transition hover:bg-secondary hover:text-neutral-black"
                                        >
                                            تکمیل پروفایل
                                        </Link>
                                    </>
                                )}
                                {enrollment.state === 'pending' && (
                                    <p className="rounded-xl bg-white/10 px-3 py-3 text-sm leading-relaxed">
                                        درخواست ثبت‌نام شما در انتظار تأیید آموزش است.
                                    </p>
                                )}
                                {enrollment.state === 'confirmed' && enrollment.student_dashboard_url && (
                                    <Link
                                        href={enrollment.student_dashboard_url}
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-white px-4 py-3 text-sm font-extrabold text-primary shadow-md transition hover:bg-secondary hover:text-neutral-black"
                                    >
                                        ورود به پنل دانشجو
                                    </Link>
                                )}
                                {enrollment.state === 'none' &&
                                    enrollment.profile_complete &&
                                    requestableIds.length > 0 && (
                                        <form
                                            onSubmit={(e) => {
                                                e.preventDefault();
                                                enrollForm.post(`/courses/${course.slug}/enrollments`, {
                                                    preserveScroll: true,
                                                });
                                            }}
                                            className="space-y-3"
                                        >
                                            {requestableIds.length > 1 && (
                                                <label className="block text-sm">
                                                    <span className="mb-1 block opacity-90">انتخاب کلاس</span>
                                                    <select
                                                        value={enrollForm.data.course_class_id}
                                                        onChange={(e) =>
                                                            enrollForm.setData('course_class_id', e.target.value)
                                                        }
                                                        className="w-full rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm text-on-primary"
                                                    >
                                                        {course.classes
                                                            .filter((c) => requestableIds.includes(c.id))
                                                            .map((c) => (
                                                                <option key={c.id} value={c.id} className="text-neutral-black">
                                                                    {c.name}
                                                                </option>
                                                            ))}
                                                    </select>
                                                </label>
                                            )}
                                            {enrollForm.errors.course_class_id && (
                                                <p className="text-xs text-red-200">{enrollForm.errors.course_class_id}</p>
                                            )}
                                            <button
                                                type="submit"
                                                disabled={enrollForm.processing || !enrollForm.data.course_class_id}
                                                className="flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-white px-4 py-3 text-sm font-extrabold text-primary shadow-md transition hover:bg-secondary hover:text-neutral-black disabled:opacity-60"
                                            >
                                                درخواست ثبت‌نام
                                            </button>
                                        </form>
                                    )}
                                {enrollment.state === 'none' &&
                                    enrollment.profile_complete &&
                                    requestableIds.length === 0 && (
                                        <p className="text-sm leading-relaxed opacity-90">
                                            در حال حاضر کلاسی برای ثبت‌نام آنلاین باز نیست. با پشتیبانی آموزش تماس بگیرید.
                                        </p>
                                    )}
                                {contactHref ? (
                                    <a
                                        href={contactHref}
                                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-white px-4 py-3 text-sm font-extrabold text-primary shadow-md transition hover:bg-secondary hover:text-neutral-black"
                                    >
                                        <Phone className="size-4" />
                                        {contactCta}
                                    </a>
                                ) : (
                                    <p className="rounded-xl bg-white/10 px-3 py-2 text-center text-xs opacity-90">
                                        شماره تماس در تنظیمات سایت ثبت نشده است.
                                    </p>
                                )}
                                <a
                                    href="#course-comments"
                                    className="flex w-full items-center justify-center gap-2 rounded-xl border border-white/35 px-4 py-2.5 text-sm font-semibold transition hover:bg-white/10"
                                >
                                    <MessageSquare className="size-4" />
                                    نظرات دوره
                                    {course.comments_summary.count > 0 && (
                                        <span className="rounded-full bg-white/20 px-2 py-0.5 text-xs">
                                            {course.comments_summary.count.toLocaleString('fa-IR')}
                                        </span>
                                    )}
                                </a>
                                {phone && (
                                    <p className="text-center text-xs opacity-75" dir="ltr">
                                        {phone}
                                    </p>
                                )}
                            </div>
                        </div>
                    </aside>
                </div>

                <section className="mt-12 rounded-2xl border border-secondary bg-surface-muted/50 p-6 sm:mt-14 sm:p-8">
                    <CourseCommentsSection
                        courseSlug={course.slug}
                        comments={course.comments}
                        summary={course.comments_summary}
                    />
                </section>
            </div>
        </AppLayout>
    );
}
