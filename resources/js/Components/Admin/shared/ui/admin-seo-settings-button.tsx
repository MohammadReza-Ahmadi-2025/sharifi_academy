import { AdminButton } from '@/Components/Admin/shared/ui/button';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { Settings2 } from 'lucide-react';

type Props = {
    href?: string;
    label?: string;
    /** If omitted, shows when the user can open site SEO settings. */
    visible?: boolean;
};

export function AdminSeoSettingsButton({
    href = '/admin/settings?group=seo',
    label = 'سئو و محتوا',
    visible,
}: Props) {
    const { auth } = usePage<PageProps>().props;
    const permissions = auth.user?.permissions ?? [];
    const canOpen =
        visible ??
        (permissions.includes('site-settings-index') || permissions.includes('site-settings-update'));

    if (!canOpen) {
        return null;
    }

    return (
        <AdminButton variant="outline" href={href}>
            <Settings2 className="size-4" />
            {label}
        </AdminButton>
    );
}
