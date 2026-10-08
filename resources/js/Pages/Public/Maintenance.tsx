import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteTheme } from '@/Components/Site/SiteTheme';
import { siteSettingsFromPage } from '@/lib/site-settings';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export default function Maintenance() {
    const props = usePage<PageProps>().props;
    const settings = siteSettingsFromPage(props);

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-surface-muted px-4 text-center">
            <SiteTheme />
            <SiteDefaultHead title="در حال به‌روزرسانی" />
            <div className="max-w-md space-y-4">
                {settings.site_logo ? (
                    <img src={settings.site_logo} alt="" className="mx-auto h-14 w-auto object-contain" />
                ) : null}
                <h1 className="text-2xl font-bold text-neutral-black">{props.app.name}</h1>
                <p className="text-slate-600">
                    {settings.site_description ?? 'سایت موقتاً در دسترس نیست. به‌زودی برمی‌گردیم.'}
                </p>
                <Link
                    href="/login"
                    className="inline-block rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-hover"
                >
                    ورود مدیران
                </Link>
            </div>
        </div>
    );
}
