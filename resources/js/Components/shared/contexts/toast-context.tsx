import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';
import type { ConfirmToastInput, ShowToastInput, ToastItem, ToastVariant } from '@/types/toast';

type ToastInput = ShowToastInput | string;

type ToastContextValue = {
    toasts: ToastItem[];
    show: (variant: ToastVariant, input: ShowToastInput) => void;
    dismiss: (id: string) => void;
    success: (input: ToastInput) => void;
    error: (input: ToastInput) => void;
    info: (input: ToastInput) => void;
    warning: (input: ToastInput) => void;
    confirm: (input: ConfirmToastInput) => void;
};

const ToastContext = createContext<ToastContextValue | null>(null);

const TOAST_DURATION_MS = 4800;

function normalizeInput(input: ToastInput): ShowToastInput {
    if (typeof input === 'string') {
        return { title: input };
    }

    return input;
}

export function ToastProvider({ children }: { children: ReactNode }) {
    const [toasts, setToasts] = useState<ToastItem[]>([]);
    const timersRef = useRef<Map<string, number>>(new Map());

    const dismiss = useCallback((id: string) => {
        setToasts((current) =>
            current.map((toast) => (toast.id === id ? { ...toast, exiting: true } : toast)),
        );

        window.setTimeout(() => {
            setToasts((current) => current.filter((toast) => toast.id !== id));
            const timerId = timersRef.current.get(id);
            if (timerId) {
                window.clearTimeout(timerId);
                timersRef.current.delete(id);
            }
        }, 320);
    }, []);

    const show = useCallback(
        (variant: ToastVariant, input: ShowToastInput, extra?: Partial<Pick<ToastItem, 'persistent' | 'actions'>>) => {
            const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

            setToasts((current) =>
                [...current, { id, variant, exiting: false, ...input, ...extra }].slice(-3),
            );

            if (!extra?.persistent) {
                const timerId = window.setTimeout(() => {
                    dismiss(id);
                }, TOAST_DURATION_MS);

                timersRef.current.set(id, timerId);
            }
        },
        [dismiss],
    );

    const confirm = useCallback(
        (input: ConfirmToastInput) => {
            const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
            const variant = input.variant ?? 'warning';

            const runAndDismiss = (callback?: () => void) => {
                dismiss(id);
                callback?.();
            };

            setToasts((current) =>
                [
                    ...current,
                    {
                        id,
                        variant,
                        title: input.title,
                        description: input.description,
                        exiting: false,
                        persistent: true,
                        actions: [
                            {
                                label: input.cancelLabel ?? 'خیر',
                                variant: 'ghost',
                                onClick: () => runAndDismiss(input.onCancel),
                            },
                            {
                                label: input.confirmLabel ?? 'بله',
                                variant: 'danger',
                                onClick: () => runAndDismiss(input.onConfirm),
                            },
                        ],
                    },
                ].slice(-3),
            );
        },
        [dismiss],
    );

    const createHandler = useCallback(
        (variant: ToastVariant) => (input: ToastInput) => {
            show(variant, normalizeInput(input));
        },
        [show],
    );

    const value = useMemo(
        () => ({
            toasts,
            show,
            dismiss,
            success: createHandler('success'),
            error: createHandler('error'),
            info: createHandler('info'),
            warning: createHandler('warning'),
            confirm,
        }),
        [toasts, show, dismiss, createHandler, confirm],
    );

    return <ToastContext.Provider value={value}>{children}</ToastContext.Provider>;
}

export function useToast() {
    const context = useContext(ToastContext);

    if (!context) {
        throw new Error('useToast must be used within ToastProvider');
    }

    return context;
}
