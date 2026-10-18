export type ToastVariant = 'success' | 'error' | 'info' | 'warning';

export type ToastActionVariant = 'primary' | 'danger' | 'ghost';

export type ToastAction = {
    label: string;
    variant?: ToastActionVariant;
    onClick: () => void;
};

export type ToastItem = {
    id: string;
    variant: ToastVariant;
    title: string;
    description?: string;
    exiting?: boolean;
    /** بدون تایمر خودکار (مثلاً تأیید حذف) */
    persistent?: boolean;
    actions?: ToastAction[];
};

export type ShowToastInput = {
    title: string;
    description?: string;
};

export type ConfirmToastInput = {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: ToastVariant;
    onConfirm: () => void;
    onCancel?: () => void;
};
