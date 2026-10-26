import { Link } from '@inertiajs/react';

export type CourseCatalogCard = {
    id: number;
    title: string;
    slug: string;
    description?: string | null;
    level_label?: string | null;
    price_from?: number | null;
    image_url?: string | null;
    is_featured?: boolean;
};

type Props = {
    courses: CourseCatalogCard[];
    variant?: 'default' | 'category';
};

export function CourseCatalogGrid({ courses, variant = 'default' }: Props) {
    if (courses.length === 0) {
        return (
            <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-16 text-center text-sm text-neutral-black/65">
                در این دسته هنوز دوره‌ای منتشر نشده است.
            </p>
        );
    }

    const gridClass =
        variant === 'category'
            ? 'grid gap-6 sm:grid-cols-2 xl:grid-cols-3'
            : 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3';

    const cardClass =
        variant === 'category'
            ? 'group flex flex-col overflow-hidden rounded-2xl border border-secondary/70 bg-neutral-white shadow-sm ring-1 ring-black/[0.03] transition hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg'
            : 'group flex flex-col overflow-hidden rounded-xl border border-secondary/80 bg-neutral-white shadow-md transition hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-lg';

    return (
        <div className={gridClass}>
            {courses.map((course) => (
                <Link
                    key={course.id}
                    href={`/courses/${course.slug}`}
                    className={cardClass}
                >
                    <div
                        className={`overflow-hidden bg-primary/10 ${
                            variant === 'category' ? 'aspect-[4/3]' : 'aspect-[5/4]'
                        }`}
                    >
                        <img
                            src={course.image_url?.trim() || '/banner.png'}
                            alt={course.title}
                            loading={variant === 'category' ? 'lazy' : undefined}
                            decoding="async"
                            className="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        />
                    </div>
                    <div className={`flex flex-1 flex-col ${variant === 'category' ? 'p-5' : 'p-4'}`}>
                        <div className="mb-2 flex flex-wrap gap-2">
                            {course.is_featured && (
                                <span
                                    className={`rounded-md bg-primary/10 font-bold text-primary ${
                                        variant === 'category' ? 'px-2.5 py-1 text-xs' : 'px-2 py-0.5 text-[11px]'
                                    }`}
                                >
                                    ویژه
                                </span>
                            )}
                            {course.level_label && (
                                <span
                                    className={`rounded-md bg-surface-muted font-semibold text-neutral-black/70 ${
                                        variant === 'category' ? 'px-2.5 py-1 text-xs' : 'px-2 py-0.5 text-[11px]'
                                    }`}
                                >
                                    {course.level_label}
                                </span>
                            )}
                        </div>
                        <h3
                            className={`line-clamp-2 font-extrabold text-neutral-black ${
                                variant === 'category' ? 'text-base sm:text-lg' : ''
                            }`}
                        >
                            {course.title}
                        </h3>
                        {course.description && (
                            <p
                                className={`mt-2 line-clamp-2 text-neutral-black/65 ${
                                    variant === 'category' ? 'text-sm sm:text-[15px]' : 'text-sm'
                                }`}
                            >
                                {course.description}
                            </p>
                        )}
                        {typeof course.price_from === 'number' && course.price_from > 0 && (
                            <p
                                className={`mt-auto pt-3 font-extrabold text-primary ${
                                    variant === 'category' ? 'text-base' : 'text-sm'
                                }`}
                            >
                                از {course.price_from.toLocaleString('fa-IR')} ریال
                            </p>
                        )}
                    </div>
                </Link>
            ))}
        </div>
    );
}
