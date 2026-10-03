import { useToast } from '@/Components/shared/contexts/toast-context';
import { useEffect, useRef } from 'react';

type FlashPayload = {
    success?: string | null;
    error?: string | null;
};

function readInitialFlash(): FlashPayload | null {
    const script = document.querySelector<HTMLScriptElement>('script[data-page][type="application/json"]');

    if (!script?.textContent) {
        return null;
    }

    try {
        const page = JSON.parse(script.textContent) as {
            props?: { flash?: FlashPayload };
        };

        return page.props?.flash ?? null;
    } catch {
        return null;
    }
}

export function InertiaFlashToasts() {
    const toast = useToast();
    const lastFlashRef = useRef({ success: null as string | null, error: null as string | null });

    const showFlash = (flash: FlashPayload | null | undefined) => {
        if (!flash) {
            return;
        }

        if (flash.success && flash.success !== lastFlashRef.current.success) {
            toast.success(flash.success);
            lastFlashRef.current.success = flash.success;
        }

        if (flash.error && flash.error !== lastFlashRef.current.error) {
            toast.error(flash.error);
            lastFlashRef.current.error = flash.error;
        }

        if (!flash.success) {
            lastFlashRef.current.success = null;
        }

        if (!flash.error) {
            lastFlashRef.current.error = null;
        }
    };

    useEffect(() => {
        showFlash(readInitialFlash());
    }, [toast]);

    useEffect(() => {
        const onFlash = (event: Event) => {
            const detail = (event as CustomEvent<{ flash?: FlashPayload }>).detail;
            showFlash(detail?.flash);
        };

        const onSuccess = (event: Event) => {
            const page = (event as CustomEvent<{ page?: { flash?: FlashPayload } }>).detail?.page;
            showFlash(page?.flash);
        };

        const onValidationError = (event: Event) => {
            const custom = event as CustomEvent<{ errors: Record<string, string | string[]> }>;
            const values = Object.values(custom.detail?.errors ?? {}).flat();
            const first = values.find((value) => typeof value === 'string' && value.length > 0);

            if (first) {
                toast.error(String(first));
            } else {
                toast.error('لطفاً خطاهای فرم را بررسی کنید.');
            }
        };

        document.addEventListener('inertia:flash', onFlash);
        document.addEventListener('inertia:success', onSuccess);
        document.addEventListener('inertia:error', onValidationError);

        return () => {
            document.removeEventListener('inertia:flash', onFlash);
            document.removeEventListener('inertia:success', onSuccess);
            document.removeEventListener('inertia:error', onValidationError);
        };
    }, [toast]);

    return null;
}
