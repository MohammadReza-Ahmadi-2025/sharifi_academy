import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';
import type { ShowToastInput, ToastItem, ToastVariant } from '@/types/toast';

type ToastInput = ShowToastInput | string;

type ToastContextValue = {
    toasts: ToastItem[];
    show: (variant: ToastVariant, input: ShowToastInput) => void;
    dismiss: (id: string) => void;
    success: (input: ToastInput) => void;
    error: (input: ToastInput) => void;
    info: (input: ToastInput) => void;
    warning: (input: ToastInput) => void;
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
        (variant: ToastVariant, input: ShowToastInput) => {
            const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

            setToasts((current) =>
                [...current, { id, variant, exiting: false, ...input }].slice(-3),
            );

            const timerId = window.setTimeout(() => {
                dismiss(id);
            }, TOAST_DURATION_MS);

            timersRef.current.set(id, timerId);
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
        }),
        [toasts, show, dismiss, createHandler],
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
