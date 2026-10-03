export type ToastVariant = 'success' | 'error' | 'info' | 'warning';

export type ToastItem = {
    id: string;
    variant: ToastVariant;
    title: string;
    description?: string;
    exiting?: boolean;
};

export type ShowToastInput = {
    title: string;
    description?: string;
};
