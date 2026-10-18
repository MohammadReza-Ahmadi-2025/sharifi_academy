import { useToast } from '@/Components/shared/contexts/toast-context';
import { useCallback } from 'react';

type Options = {
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
};

/**
 * جایگزین window.confirm — سؤال در توست با «بله» / «خیر»
 */
export function useConfirmToast() {
    const toast = useToast();

    return useCallback(
        (title: string, onConfirm: () => void, options?: Options) => {
            toast.confirm({
                title,
                description: options?.description,
                confirmLabel: options?.confirmLabel ?? 'بله',
                cancelLabel: options?.cancelLabel ?? 'خیر',
                variant: 'warning',
                onConfirm,
            });
        },
        [toast],
    );
}
