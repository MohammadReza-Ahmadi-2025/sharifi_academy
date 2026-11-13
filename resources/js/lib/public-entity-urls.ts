export function blogPostPath(id: number, slug: string): string {
    return `/blog/${id}/${slug}`;
}

export function coursePath(id: number, slug: string): string {
    return `/courses/${id}/${slug}`;
}
