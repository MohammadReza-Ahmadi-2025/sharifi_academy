export type BreadcrumbItem = {
    label: string;
    href?: string;
};

export function getAdminBreadcrumbs(pathname: string): BreadcrumbItem[] {
    const items: BreadcrumbItem[] = [{ label: 'پنل مدیریت', href: '/admin' }];

    if (pathname === '/admin' || pathname === '/admin/') {
        items.push({ label: 'داشبورد' });

        return items;
    }

    const segment = pathname.replace(/^\/admin\/?/, '').split('/')[0];
    const labels: Record<string, string> = {
        users: 'کاربران',
        roles: 'نقش‌ها و دسترسی‌ها',
        courses: 'دوره‌ها',
        books: 'کتاب‌ها',
        orders: 'سفارش‌ها',
    };

    if (segment === 'profile') {
        items.push({ label: 'پروفایل من' });

        return items;
    }

    if (segment === 'users') {
        items.push({ label: labels.users, href: '/admin/users' });

        if (pathname.includes('/create')) {
            items.push({ label: 'ایجاد کاربر' });
        } else if (pathname.includes('/edit')) {
            items.push({ label: 'ویرایش کاربر' });
        }

        return items;
    }

    if (segment === 'roles') {
        items.push({ label: labels.roles, href: '/admin/roles' });

        if (pathname.includes('/create')) {
            items.push({ label: 'ایجاد نقش' });
        } else if (pathname.includes('/edit')) {
            items.push({ label: 'ویرایش نقش' });
        }

        return items;
    }

    items.push({ label: labels[segment] ?? (segment || 'صفحه') });

    return items;
}
