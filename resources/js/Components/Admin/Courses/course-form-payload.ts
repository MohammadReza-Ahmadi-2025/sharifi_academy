export type CourseFormPayloadInput = {
    title: string;
    slug: string;
    code: string;
    short_description: string;
    description: string;
    level: string;
    duration_minutes: number | '';
    status: string;
    is_featured: boolean;
    published_at: string;
    category_ids: number[];
};

export function normalizeCourseFormPayload<T extends CourseFormPayloadInput>(data: T): T {
    return {
        ...data,
        slug: data.slug.trim() || null,
        code: data.code.trim() || null,
        level: data.level || null,
        short_description: data.short_description.trim() || null,
        description: data.description.trim() || null,
        duration_minutes: data.duration_minutes === '' ? null : data.duration_minutes,
        published_at: data.published_at || null,
        category_ids: data.category_ids ?? [],
    } as T;
}
