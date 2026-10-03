export type BreadcrumbItem = {
    label: string;
    href?: string;
};

const SETTING_GROUP_LABELS: Record<string, string> = {
    general: 'عمومی',
    contact: 'تماس و پشتیبانی',
    seo: 'سئو و محتوا',
    integrations: 'کلیدها و یکپارچه‌سازی',
};

const CATEGORY_TYPE_LABELS: Record<string, string> = {
    product: 'محصول',
    blog: 'وبلاگ',
    course: 'دوره',
    faq: 'سوالات متداول',
    service: 'خدمات',
};

function categoryTypeLabel(type: string | null): string {
    if (!type) {
        return 'دسته‌بندی‌ها';
    }

    return CATEGORY_TYPE_LABELS[type] ?? 'دسته‌بندی‌ها';
}

function parseUrl(urlOrPath: string): { segments: string[]; searchParams: URLSearchParams } {
    const withoutOrigin = urlOrPath.replace(/^https?:\/\/[^/]+/i, '');
    const [pathPart, queryPart] = withoutOrigin.split('?');
    const segments = pathPart.replace(/^\/admin\/?/, '').split('/').filter(Boolean);

    return {
        segments,
        searchParams: new URLSearchParams(queryPart ?? ''),
    };
}

function pushCrumb(items: BreadcrumbItem[], label: string, href?: string): void {
    items.push(href ? { label, href } : { label });
}

export function getAdminBreadcrumbs(urlOrPath: string): BreadcrumbItem[] {
    const items: BreadcrumbItem[] = [{ label: 'پنل مدیریت', href: '/admin' }];
    const { segments, searchParams } = parseUrl(urlOrPath);

    if (segments.length === 0) {
        pushCrumb(items, 'داشبورد');

        return items;
    }

    const [first, second, third, fourth, fifth, sixth] = segments;

    if (first === 'profile') {
        pushCrumb(items, 'پروفایل من');

        return items;
    }

    if (first === 'users') {
        pushCrumb(items, 'کاربران', '/admin/users');
        if (second === 'create') {
            pushCrumb(items, 'ایجاد کاربر');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش کاربر');
        }

        return items;
    }

    if (first === 'roles') {
        pushCrumb(items, 'نقش‌ها و دسترسی‌ها', '/admin/roles');
        if (second === 'create') {
            pushCrumb(items, 'ایجاد نقش');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش نقش');
        }

        return items;
    }

    if (first === 'categories') {
        const type = searchParams.get('type');
        const typeLabel = categoryTypeLabel(type);
        const indexHref = type ? `/admin/categories?type=${type}` : '/admin/categories';

        pushCrumb(items, 'دسته‌بندی‌ها', '/admin/categories');
        if (second === 'create') {
            pushCrumb(items, `ایجاد دسته ${typeLabel}`);
        } else if (second && third === 'edit') {
            pushCrumb(items, `ویرایش دسته ${typeLabel}`);
        } else {
            pushCrumb(items, typeLabel);
        }

        return items;
    }

    if (first === 'catalog' && second === 'products') {
        pushCrumb(items, 'محصولات', '/admin/catalog/products');
        if (third === 'create') {
            pushCrumb(items, 'ایجاد محصول');
        } else if (third && fourth === 'edit') {
            pushCrumb(items, 'ویرایش محصول');
        }

        return items;
    }

    if (first === 'catalog' && second === 'brands') {
        pushCrumb(items, 'برندها', '/admin/catalog/brands');
        if (third === 'create') {
            pushCrumb(items, 'ایجاد برند');
        } else if (third && fourth === 'edit') {
            pushCrumb(items, 'ویرایش برند');
        }

        return items;
    }

    if (first === 'catalog' && second === 'warehouses') {
        pushCrumb(items, 'انبارها', '/admin/catalog/warehouses');
        if (third === 'create') {
            pushCrumb(items, 'ایجاد انبار');
        } else if (third && fourth === 'edit') {
            pushCrumb(items, 'ویرایش انبار');
        }

        return items;
    }

    if (first === 'catalog' && second === 'inventory') {
        pushCrumb(items, 'موجودی انبار');

        return items;
    }

    if (first === 'skills') {
        pushCrumb(items, 'مهارت‌ها', '/admin/skills');
        if (second === 'create') {
            pushCrumb(items, 'ایجاد مهارت');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش مهارت');
        }

        return items;
    }

    if (first === 'locations') {
        pushCrumb(items, 'استان و شهر', '/admin/locations');
        if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش شهرها');
        }

        return items;
    }

    if (first === 'files') {
        pushCrumb(items, 'فایل‌ها');

        return items;
    }

    if (first === 'session-attendances') {
        pushCrumb(items, 'حضور و غیاب', '/admin/session-attendances');
        if (second && !third) {
            pushCrumb(items, 'ثبت حضور جلسه');
        }

        return items;
    }

    if (first === 'activity-logs') {
        pushCrumb(items, 'گزارش فعالیت', '/admin/activity-logs');
        if (second && !third) {
            pushCrumb(items, 'جزئیات گزارش');
        }

        return items;
    }

    if (first === 'courses') {
        pushCrumb(items, 'دوره‌ها', '/admin/courses');

        if (!second) {
            return items;
        }

        if (second === 'create') {
            pushCrumb(items, 'ایجاد دوره');

            return items;
        }

        if (third === 'edit') {
            pushCrumb(items, 'ویرایش دوره');

            return items;
        }

        if (second && !third) {
            pushCrumb(items, 'جزئیات دوره');

            return items;
        }

        if (third === 'classes') {
            pushCrumb(items, 'کلاس‌ها');
            if (fourth === 'create') {
                pushCrumb(items, 'ایجاد کلاس');
            } else if (fourth && fifth === 'edit') {
                pushCrumb(items, 'ویرایش کلاس');
            } else if (fourth && fifth === 'sessions') {
                pushCrumb(items, 'جلسات');
                if (sixth === 'create') {
                    pushCrumb(items, 'ایجاد جلسه');
                } else if (sixth && segments[7] === 'edit') {
                    pushCrumb(items, 'ویرایش جلسه');
                }
            }

            return items;
        }

        return items;
    }

    if (first === 'socials') {
        pushCrumb(items, 'شبکه‌های اجتماعی', '/admin/socials');
        if (second === 'create') {
            pushCrumb(items, 'ایجاد آیتم');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش آیتم');
        }

        return items;
    }

    if (first === 'comments') {
        pushCrumb(items, 'نظرات', '/admin/comments');
        if (second === 'create') {
            pushCrumb(items, 'ثبت نظر');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش نظر');
        }

        return items;
    }

    if (first === 'blog' && second === 'posts') {
        pushCrumb(items, 'پست‌های وبلاگ', '/admin/blog/posts');
        if (third === 'create') {
            pushCrumb(items, 'ایجاد پست');
        } else if (third && fourth === 'edit') {
            pushCrumb(items, 'ویرایش پست');
        }

        return items;
    }

    if (first === 'menus') {
        pushCrumb(items, 'منوهای سایت', '/admin/menus');
        if (second === 'create') {
            pushCrumb(items, 'ایجاد منو');
        } else if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش منو');
        }

        return items;
    }

    if (first === 'visits') {
        pushCrumb(items, 'بازدیدها', '/admin/visits');

        return items;
    }

    if (first === 'stories') {
        pushCrumb(items, 'استوری‌ها', '/admin/stories');

        return items;
    }

    if (first === 'settings') {
        const group = searchParams.get('group');
        const indexHref = group ? `/admin/settings?group=${group}` : '/admin/settings';

        pushCrumb(items, 'تنظیمات سایت', '/admin/settings');
        if (second && third === 'edit') {
            pushCrumb(items, 'ویرایش تنظیم');
        } else if (group) {
            pushCrumb(items, SETTING_GROUP_LABELS[group] ?? group, indexHref);
        }

        return items;
    }

    const fallbackLabels: Record<string, string> = {
        courses: 'دوره‌ها',
        catalog: 'کاتالوگ',
        blog: 'وبلاگ',
        socials: 'شبکه‌های اجتماعی',
        comments: 'نظرات',
        stories: 'استوری‌ها',
        products: 'محصولات',
    };

    pushCrumb(items, fallbackLabels[first] ?? first);

    return items;
}
