export type HomeContentType =
    | 'features'
    | 'product'
    | 'course'
    | 'post'
    | 'brand'
    | 'category'
    | 'banner';

export function homeCatalogHrefPrefix(contentType: string): string {
    switch (contentType) {
        case 'course':
            return '/courses/';
        case 'product':
            return '/books/';
        case 'post':
            return '/blog/';
        case 'brand':
            return '/brands/';
        case 'category':
            return '/categories/';
        default:
            return '/books/';
    }
}

export function homeCatalogEmptyMessage(contentType: string): string {
    switch (contentType) {
        case 'course':
            return 'هنوز دوره‌ای منتشر نشده است.';
        case 'product':
            return 'هنوز کتابی منتشر نشده است.';
        case 'post':
            return 'هنوز پستی منتشر نشده است.';
        case 'brand':
            return 'برندی ثبت نشده است.';
        case 'category':
            return 'دسته‌بندی‌ای وجود ندارد.';
        default:
            return 'موردی برای نمایش نیست.';
    }
}

export function homeCatalogShowPrice(contentType: string): boolean {
    return contentType === 'product' || contentType === 'course';
}
