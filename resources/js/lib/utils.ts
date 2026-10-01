import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/** ارقام فارسی و عربی را به ۰–۹ لاتین تبدیل می‌کند */
export function toWesternDigits(value: string): string {
    return value.replace(/[۰-۹٠-٩]/g, (char) => {
        const code = char.charCodeAt(0);

        if (code >= 0x06f0 && code <= 0x06f9) {
            return String(code - 0x06f0);
        }

        if (code >= 0x0660 && code <= 0x0669) {
            return String(code - 0x0660);
        }

        return char;
    });
}

export function normalizePhoneInput(value: string): string {
    return toWesternDigits(value).replace(/\D/g, '').slice(0, 11);
}

export function normalizeOtpDigit(value: string): string {
    return toWesternDigits(value).replace(/\D/g, '').slice(-1);
}
