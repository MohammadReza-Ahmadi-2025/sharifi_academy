import type { TextareaHTMLAttributes } from 'react';

type CkEditorInstance = {
    getData: () => string;
    setData: (data: string) => void;
    on: (event: string, callback: () => void) => void;
    destroy: () => void;
};

type CkEditorStatic = {
    replace: (element: HTMLTextAreaElement | string, config?: Record<string, unknown>) => CkEditorInstance;
    instances: Record<string, CkEditorInstance>;
};

declare global {
    interface Window {
        CKEDITOR?: CkEditorStatic;
        CKEDITOR_BASEPATH?: string;
    }
}

let loadPromise: Promise<CkEditorStatic> | null = null;

function appendScript(src: string): Promise<void> {
    return new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) {
            resolve();

            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = false;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
        document.head.appendChild(script);
    });
}

export function loadCkEditor4(): Promise<CkEditorStatic> {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('CKEditor is only available in the browser.'));
    }

    if (window.CKEDITOR) {
        return Promise.resolve(window.CKEDITOR);
    }

    if (!loadPromise) {
        window.CKEDITOR_BASEPATH = '/ckeditor/';
        loadPromise = appendScript('/ckeditor/ckeditor.js').then(() => {
            if (!window.CKEDITOR) {
                throw new Error('CKEditor failed to load.');
            }

            return window.CKEDITOR;
        });
    }

    return loadPromise;
}

export function ckEditorHeightFromRows(rows?: number): number {
    if (!rows || rows < 1) {
        return 220;
    }

    return Math.max(160, rows * 28);
}

export type RichTextareaNativeProps = TextareaHTMLAttributes<HTMLTextAreaElement>;
