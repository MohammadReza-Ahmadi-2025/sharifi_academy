import { toWesternDigits } from '@/lib/utils';
import { z } from 'zod';

/** @deprecated — احراز هویت با OTP جایگزین شده */
export const loginSchema = z.object({
    email: z.string().email('ایمیل معتبر نیست'),
    password: z.string().min(1, 'رمز عبور الزامی است'),
    remember: z.boolean().optional(),
});

/** @deprecated — احراز هویت با OTP جایگزین شده */
export const registerSchema = z.object({
    name: z.string().min(2, 'نام کوتاه است'),
    email: z.string().email('ایمیل معتبر نیست'),
    password: z.string().min(8, 'حداقل ۸ کاراکتر'),
    password_confirmation: z.string(),
}).refine((data) => data.password === data.password_confirmation, {
    message: 'تکرار رمز مطابقت ندارد',
    path: ['password_confirmation'],
});

export const otpPhoneSchema = z.object({
    phone: z
        .string()
        .trim()
        .transform((value) => toWesternDigits(value).replace(/\s+/g, ''))
        .pipe(z.string().regex(/^09\d{9}$/, 'شماره موبایل معتبر نیست (مثال: 09123456789)')),
});

export const otpCodeSchema = z.object({
    code: z
        .string()
        .trim()
        .transform((value) => toWesternDigits(value))
        .pipe(z.string().regex(/^\d{5,6}$/, 'کد ۵ یا ۶ رقمی را وارد کنید')),
});
