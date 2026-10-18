const FIELD_LABELS: Record<string, string> = {
    title: 'عنوان سکشن',
    thumbnail: 'پیش‌نمایش',
    media: 'فایل رسانه',
    content: 'متن',
    type: 'نوع',
    user_id: 'کاربر',
    visibility: 'نمایش',
    display_starts_at: 'شروع نمایش',
    display_ends_at: 'پایان نمایش',
};

type Props = {
    errors: Record<string, string | undefined>;
    title?: string;
};

function humanizeFieldKey(field: string): string {
    if (field.startsWith('tabs.') && field.includes('.settings.features')) {
        return 'گرید سه‌تایی';
    }
    if (field.startsWith('tabs.') && field.includes('.banners')) {
        return 'اسلایدهای بنر';
    }
    if (field.startsWith('tabs.') && field.endsWith('.limit')) {
        return 'حداکثر آیتم تب';
    }
    if (field.startsWith('settings.hero.')) {
        return 'تنظیمات هیرو';
    }

    return field;
}

export function AdminFormErrors({ errors, title = 'خطا در ذخیره فرم' }: Props) {
    const messages = Object.entries(errors).filter(
        (entry): entry is [string, string] => typeof entry[1] === 'string' && entry[1].length > 0,
    );

    if (messages.length === 0) {
        return null;
    }

    return (
        <div
            role="alert"
            className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-100"
        >
            <p className="font-semibold">{title}</p>
            <ul className="mt-2 list-disc space-y-1 ps-5">
                {messages.map(([field, message]) => (
                    <li key={field}>
                        <span className="font-medium text-red-800/80 dark:text-red-200/80">
                            {FIELD_LABELS[field] ?? humanizeFieldKey(field)}:
                        </span>{' '}
                        {message}
                    </li>
                ))}
            </ul>
        </div>
    );
}
