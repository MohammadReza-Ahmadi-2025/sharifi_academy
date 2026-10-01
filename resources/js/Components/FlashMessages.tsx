import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useEffect } from 'react';

export function FlashMessages() {
    const { flash } = usePage<PageProps>().props;

    useEffect(() => {
        if (flash.success) {
            // eslint-disable-next-line no-alert
            console.info(flash.success);
        }
    }, [flash.success]);

    if (!flash.success && !flash.error) {
        return null;
    }

    return (
        <div className="mb-4 space-y-2">
            {flash.success && (
                <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
                    {flash.error}
                </div>
            )}
        </div>
    );
}
