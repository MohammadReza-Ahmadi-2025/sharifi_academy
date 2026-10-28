import { OtpDigitInputs } from '@/Components/Auth/OtpDigitInputs';
import { otpCodeSchema, otpPhoneSchema } from '@/lib/schemas/auth';
import { normalizePhoneInput } from '@/lib/utils';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteTheme } from '@/Components/Site/SiteTheme';
import { PageProps } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, Lock, Smartphone } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

type PhoneForm = z.infer<typeof otpPhoneSchema>;
type CodeForm = z.infer<typeof otpCodeSchema>;

type AuthPageProps = PageProps & {
    errors?: Partial<Record<'phone' | 'code', string>>;
};

function formatCountdown(totalSeconds: number): string {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

function maskPhone(phone: string): string {
    if (phone.length < 4) {
        return phone;
    }

    return `${'*'.repeat(Math.max(0, phone.length - 4))}${phone.slice(-4)}`;
}

function AuthIcon() {
    return (
        <div className="otp-auth-icon-wrap" aria-hidden>
            <div className="otp-auth-icon">
                <Lock className="size-7" strokeWidth={1.75} />
            </div>
        </div>
    );
}

export default function OtpAuth() {
    const { flash, errors: serverErrors = {}, otpPolicy } = usePage<AuthPageProps>().props;
    const [step, setStep] = useState<'phone' | 'code'>('phone');
    const [phone, setPhone] = useState('');
    const [otpCode, setOtpCode] = useState('');
    const [resendIn, setResendIn] = useState(0);
    const [expiresIn, setExpiresIn] = useState(0);
    const codeExpired = step === 'code' && expiresIn <= 0;

    const phoneForm = useForm<PhoneForm>({
        resolver: zodResolver(otpPhoneSchema),
        defaultValues: { phone: '' },
        mode: 'onChange',
    });

    const codeForm = useForm<CodeForm>({
        resolver: zodResolver(otpCodeSchema),
        defaultValues: { code: '' },
    });

    const phoneValue = phoneForm.watch('phone');

    useEffect(() => {
        codeForm.setValue('code', otpCode, { shouldValidate: false });
    }, [otpCode, codeForm]);

    useEffect(() => {
        if (resendIn <= 0) {
            return;
        }

        const timer = window.setInterval(() => {
            setResendIn((value) => Math.max(0, value - 1));
        }, 1000);

        return () => window.clearInterval(timer);
    }, [resendIn]);

    useEffect(() => {
        if (step !== 'code' || expiresIn <= 0) {
            return;
        }

        const timer = window.setInterval(() => {
            setExpiresIn((value) => Math.max(0, value - 1));
        }, 1000);

        return () => window.clearInterval(timer);
    }, [step, expiresIn]);

    const sendOtp = phoneForm.handleSubmit((values) => {
        router.post(
            '/auth/otp/request',
            { phone: values.phone },
            {
                preserveState: true,
                onSuccess: () => {
                    setPhone(values.phone);
                    setStep('code');
                    setResendIn(otpPolicy.resend_cooldown_seconds);
                    setExpiresIn(otpPolicy.expires_seconds);
                    setOtpCode('');
                    codeForm.reset({ code: '' });
                },
            },
        );
    });

    const verifyOtp = codeForm.handleSubmit((values) => {
        if (codeExpired) {
            return;
        }

        router.post('/auth/otp/verify', {
            phone,
            code: values.code,
        });
    });

    const resendOtp = () => {
        if (resendIn > 0 || !phone) {
            return;
        }

        router.post(
            '/auth/otp/request',
            { phone },
            {
                preserveState: true,
                onSuccess: () => {
                    setResendIn(otpPolicy.resend_cooldown_seconds);
                    setExpiresIn(otpPolicy.expires_seconds);
                    setOtpCode('');
                    codeForm.reset({ code: '' });
                },
            },
        );
    };

    const backToPhone = () => {
        setStep('phone');
        setOtpCode('');
        setExpiresIn(0);
        codeForm.reset({ code: '' });
    };

    const phoneError = phoneForm.formState.errors.phone?.message ?? serverErrors.phone;
    const codeError = codeForm.formState.errors.code?.message ?? serverErrors.code;

    const flashMessage = (flash?.error || flash?.success) && (
        <p className={`otp-auth-flash ${flash.error ? 'otp-auth-flash--error' : 'otp-auth-flash--success'}`}>
            {flash.error ?? flash.success}
        </p>
    );

    return (
        <div className="otp-auth-shell" dir="rtl">
            <SiteTheme />
            <SiteDefaultHead title="ورود / ثبت‌نام" />

            <div className="otp-auth-stack">
                <form className="otp-auth-card" onSubmit={step === 'phone' ? sendOtp : verifyOtp}>
                    <AuthIcon />

                    {step === 'phone' ? (
                        <>
                            <h6>ورود / ثبت‌نام</h6>
                            <p className="otp-auth-sub">شماره موبایل خود را وارد کنید تا کد تأیید برایتان ارسال شود.</p>
                            <p className="otp-auth-hint">اگر حساب ندارید، پس از تأیید کد برای شما ساخته می‌شود.</p>

                            {flashMessage}

                            <div className={`otp-auth-field otp-auth-field--phone${phoneError ? ' otp-auth-field--error' : ''}`}>
                                <span className="otp-auth-field-icon">
                                    <Smartphone className="size-5" strokeWidth={1.75} />
                                </span>
                                <input
                                    id="phone"
                                    type="tel"
                                    inputMode="numeric"
                                    autoComplete="tel-national"
                                    placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                                    dir="ltr"
                                    value={phoneValue}
                                    aria-invalid={Boolean(phoneError)}
                                    onChange={(event) => {
                                        const next = normalizePhoneInput(event.target.value);
                                        phoneForm.setValue('phone', next, { shouldValidate: next.length === 11 });
                                    }}
                                    onBlur={() => phoneForm.trigger('phone')}
                                />
                            </div>
                            {phoneError && <p className="otp-auth-error">{phoneError}</p>}

                            <div className="otp-auth-actions">
                                <button
                                    type="submit"
                                    className="otp-auth-validate"
                                    disabled={phoneForm.formState.isSubmitting || phoneValue.length < 11}
                                >
                                    دریافت کد
                                </button>
                            </div>

                            <div className="otp-auth-footer">
                                <Link href="/" className="otp-auth-footer-link">
                                    <ArrowRight className="size-4 shrink-0" aria-hidden />
                                    بازگشت به صفحه اصلی
                                </Link>
                            </div>
                        </>
                    ) : (
                        <>
                            <h6>تأیید کد یکبار مصرف</h6>
                            <p className="otp-auth-sub">
                                <span>کد ارسال‌شده به </span>
                                <small dir="ltr">{maskPhone(phone)}</small>
                                <span> را وارد کنید</span>
                            </p>
                            {codeExpired ? (
                                <p className="otp-auth-hint otp-auth-hint--warn">
                                    اعتبار این کد تمام شده است. «ارسال مجدد کد» را بزنید یا شماره را ویرایش کنید.
                                </p>
                            ) : (
                                <p className="otp-auth-hint">
                                    اعتبار کد:{' '}
                                    <span dir="ltr">{formatCountdown(expiresIn)}</span>
                                </p>
                            )}

                            {flashMessage}

                            <div className={codeError ? 'otp-auth-otp-wrap otp-auth-otp-wrap--error' : 'otp-auth-otp-wrap'}>
                                <OtpDigitInputs
                                    value={otpCode}
                                    onChange={(code) => {
                                        setOtpCode(code);
                                        if (code.length >= 5) {
                                            codeForm.setValue('code', code, { shouldValidate: true });
                                        }
                                    }}
                                    disabled={codeForm.formState.isSubmitting || codeExpired}
                                />
                            </div>
                            {codeError && <p className="otp-auth-error">{codeError}</p>}

                            <div className="otp-auth-actions">
                                <button
                                    type="submit"
                                    className="otp-auth-validate"
                                    disabled={
                                        codeForm.formState.isSubmitting || otpCode.length < 5 || codeExpired
                                    }
                                >
                                    تأیید و ورود
                                </button>
                            </div>

                            <div className="otp-auth-footer otp-auth-footer--stack">
                                <p className="otp-auth-footer-text">کد را دریافت نکردید؟</p>
                                <div className="otp-auth-footer-actions">
                                    <button
                                        type="button"
                                        className="otp-auth-footer-btn"
                                        onClick={resendOtp}
                                        disabled={resendIn > 0}
                                    >
                                        {resendIn > 0 ? `ارسال مجدد (${resendIn} ثانیه)` : 'ارسال مجدد کد'}
                                    </button>
                                    <button
                                        type="button"
                                        className="otp-auth-footer-btn otp-auth-footer-btn--muted"
                                        onClick={backToPhone}
                                    >
                                        ویرایش شماره
                                    </button>
                                </div>
                            </div>
                        </>
                    )}
                </form>
            </div>
        </div>
    );
}
