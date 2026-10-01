export type HttpErrorContent = {
    title: string;
    description: string;
};

export const HTTP_ERROR_PAGES: Record<number, HttpErrorContent> = {
    400: {
        title: 'درخواست نامعتبر',
        description: 'اطلاعات ارسالی درست نیست. لطفاً دوباره تلاش کنید.',
    },
    401: {
        title: 'ورود لازم است',
        description: 'برای دیدن این صفحه باید وارد حساب کاربری شوید.',
    },
    403: {
        title: 'دسترسی مجاز نیست',
        description: 'شما اجازه دسترسی به این بخش را ندارید.',
    },
    404: {
        title: 'صفحه پیدا نشد',
        description: 'آدرسی که وارد کرده‌اید وجود ندارد یا جابه‌جا شده است.',
    },
    419: {
        title: 'نشست منقضی شد',
        description: 'برای ادامه، صفحه را یک‌بار دیگر بارگذاری کنید.',
    },
    429: {
        title: 'درخواست‌های زیاد',
        description: 'لطفاً چند لحظه صبر کنید و دوباره تلاش کنید.',
    },
    500: {
        title: 'خطای سرور',
        description: 'مشکلی در سمت سرور پیش آمد. به‌زودی بررسی می‌کنیم.',
    },
    503: {
        title: 'سایت موقتاً در دسترس نیست',
        description: 'در حال به‌روزرسانی یا نگهداری هستیم. لطفاً بعداً سر بزنید.',
    },
};

export function httpErrorContent(status: number): HttpErrorContent {
    return (
        HTTP_ERROR_PAGES[status] ?? {
            title: 'خطا',
            description: 'مشکلی پیش آمده است. لطفاً دوباره تلاش کنید.',
        }
    );
}
