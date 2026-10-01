import { HomeHeroSection } from '@/Components/Home/HomeHeroSection';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { BookMarked, MonitorPlay, Users } from 'lucide-react';
import type { ReactNode } from 'react';

type Item = { id: number; title: string; slug: string; price: number; description?: string | null };

type Props = { courses: Item[]; books: Item[] };

export default function Home({ courses, books }: Props) {
    return (
        <AppLayout fullWidth>
            <Head title="خانه" />

            <HomeHeroSection />

            <section className="mx-auto max-w-6xl px-4 py-12">
                <div className="grid gap-4 md:grid-cols-3">
                    <Feature
                        icon={<MonitorPlay className="size-5" />}
                        title="کلاس آنلاین زنده"
                        text="ورود به جلسه از پنل دانشجو با لینک Adobe Connect."
                    />
                    <Feature
                        icon={<BookMarked className="size-5" />}
                        title="کتاب تخصصی"
                        text="خرید و دانلود امن PDF کتاب‌های آموزشی آکادمی."
                    />
                    <Feature
                        icon={<Users className="size-5" />}
                        title="اساتید مجرب"
                        text="هر دوره یک استاد مسئول دارد و جلسات را خودش مدیریت می‌کند."
                    />
                </div>
            </section>

            <Catalog
                id="courses"
                title="دوره‌های آموزشی"
                subtitle="کلاس‌های تخصصی مهندسی برای ثبت‌نام آنلاین"
                items={courses}
                hrefPrefix="/courses/"
                empty="هنوز دوره‌ای منتشر نشده است."
            />

            <Catalog
                id="books"
                title="کتاب‌های آکادمی"
                subtitle="منابع مکتوب برای تکمیل مسیر یادگیری"
                items={books}
                hrefPrefix="/books/"
                empty="هنوز کتابی منتشر نشده است."
            />
        </AppLayout>
    );
}

function Feature({ icon, title, text }: { icon: ReactNode; title: string; text: string }) {
    return (
        <div className="rounded-2xl border border-secondary bg-neutral-white p-5 shadow-sm">
            <div className="mb-3 inline-flex rounded-xl bg-primary p-2 text-secondary">{icon}</div>
            <h3 className="font-bold text-neutral-black">{title}</h3>
            <p className="mt-1 text-sm text-neutral-black/70">{text}</p>
        </div>
    );
}

function Catalog({
    id,
    title,
    subtitle,
    items,
    hrefPrefix,
    empty,
}: {
    id: string;
    title: string;
    subtitle: string;
    items: Item[];
    hrefPrefix: string;
    empty: string;
}) {
    return (
        <section id={id} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-10">
            <div className="mb-6">
                <h2 className="text-2xl font-extrabold text-neutral-black">{title}</h2>
                <p className="mt-1 text-sm text-neutral-black/70">{subtitle}</p>
            </div>
            {items.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-10 text-center text-sm text-neutral-black/70">
                    {empty}
                </p>
            ) : (
                <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {items.map((item) => (
                        <Link
                            key={item.id}
                            href={`${hrefPrefix}${item.slug}`}
                            className="group overflow-hidden rounded-2xl border border-secondary bg-neutral-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div className="aspect-4/3 overflow-hidden bg-primary">
                                <img
                                    src="/coursetest.png"
                                    alt={item.title}
                                    className="size-full object-cover transition duration-300 group-hover:scale-105"
                                />
                            </div>
                            <div className="p-4">
                                <h3 className="font-bold text-neutral-black">{item.title}</h3>
                                <p className="mt-2 line-clamp-2 text-sm text-neutral-black/70">{item.description}</p>
                                <p className="mt-4 text-sm font-extrabold text-primary">
                                    {item.price.toLocaleString('fa-IR')} ریال
                                </p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </section>
    );
}
