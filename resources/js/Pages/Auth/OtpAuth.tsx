import { OtpDigitInputs } from '@/Components/Auth/OtpDigitInputs';
import { otpCodeSchema, otpPhoneSchema, passwordLoginSchema } from '@/lib/schemas/auth';
import { normalizePhoneInput } from '@/lib/utils';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteTheme } from '@/Components/Site/SiteTheme';
import { PageProps } from '@/types';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, KeyRound, Lock, Smartphone } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

type PhoneForm = z.infer<typeof otpPhoneSchema>;
type CodeForm = z.infer<typeof otpCodeSchema>;
type PasswordForm = z.infer<typeof passwordLoginSchema>;

const RESEND_SECONDS = 120;

type AuthTab = 'otp' | 'password';

type AuthPageProps = PageProps & {
    errors?: Partial<Record<'phone' | 'code' | 'password', string>>;
};

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

function AuthTabs({ tab, onChange, showTabs }: { tab: AuthTab; onChange: (tab: AuthTab) => void; showTabs: boolean }) {
    if (!showTabs) {
        return null;
    }

    return (
        <div className="otp-auth-tabs" role="tablist" aria-label="روش ورود">
            <button
                type="button"
                role="tab"
                aria-selected={tab === 'otp'}
                className={`otp-auth-tab${tab === 'otp' ? ' otp-auth-tab--active' : ''}`}
                onClick={() => onChange('otp')}
            >
                <Smartphone className="size-4 shrink-0" aria-hidden />
                ورود با کد
            </button>
            <button
                type="button"
                role="tab"
                aria-selected={tab === 'password'}
                className={`otp-auth-tab${tab === 'password' ? ' otp-auth-tab--active' : ''}`}
                onClick={() => onChange('password')}
            >
                <KeyRound className="size-4 shrink-0" aria-hidden />
                ورود با رمز
            </button>
        </div>
    );
}

export default function OtpAuth() {
    const { flash, errors: serverErrors = {} } = usePage<AuthPageProps>().props;
    const [authTab, setAuthTab] = useState<AuthTab>('otp');
    const [step, setStep] = useState<'phone' | 'code'>('phone');
    const [phone, setPhone] = useState('');
    const [otpCode, setOtpCode] = useState('');
    const [resendIn, setResendIn] = useState(0);

    const phoneForm = useForm<PhoneForm>({
        resolver: zodResolver(otpPhoneSchema),
        defaultValues: { phone: '' },
        mode: 'onChange',
    });

    const codeForm = useForm<CodeForm>({
        resolver: zodResolver(otpCodeSchema),
        defaultValues: { code: '' },
    });

    const passwordForm = useForm<PasswordForm>({
        resolver: zodResolver(passwordLoginSchema),
        defaultValues: { phone: '', password: '' },
        mode: 'onChange',
    });

    const phoneValue = phoneForm.watch('phone');
    const passwordPhoneValue = passwordForm.watch('phone');

    useEffect(() => {
        codeForm.setValue('code', otpCode, { shouldValidate: false });
    }, [otpCode]);

    useEffect(() => {
        if (resendIn <= 0) {
            return;
        }

        const timer = window.setInterval(() => {
            setResendIn((value) => Math.max(0, value - 1));
        }, 1000);

        return () => window.clearInterval(timer);
    }, [resendIn]);

    const sendOtp = phoneForm.handleSubmit((values) => {
        router.post(
            '/auth/otp/request',
            { phone: values.phone },
            {
                preserveState: true,
                onSuccess: () => {
                    setPhone(values.phone);
                    setStep('code');
                    setResendIn(RESEND_SECONDS);
                    setOtpCode('');
                    codeForm.reset({ code: '' });
                },
            },
        );
    });

    const verifyOtp = codeForm.handleSubmit((values) => {
        router.post('/auth/otp/verify', {
            phone,
            code: values.code,
        });
    });

    const loginWithPassword = passwordForm.handleSubmit((values) => {
        router.post('/auth/password/login', {
            phone: values.phone,
            password: values.password,
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
                onSuccess: () => setResendIn(RESEND_SECONDS),
            },
        );
    };

    const backToPhone = () => {
        setStep('phone');
        setOtpCode('');
        codeForm.reset({ code: '' });
    };

    const phoneError = phoneForm.formState.errors.phone?.message ?? serverErrors.phone;
    const codeError = codeForm.formState.errors.code?.message ?? serverErrors.code;
    const passwordPhoneError = passwordForm.formState.errors.phone?.message ?? serverErrors.phone;
    const passwordError = passwordForm.formState.errors.password?.message ?? serverErrors.password;

    const showTabs = authTab === 'password' || step === 'phone';

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
                {authTab === 'password' ? (
                    <form className="otp-auth-card" onSubmit={loginWithPassword}>
                        <AuthIcon />
                        <AuthTabs tab={authTab} onChange={setAuthTab} showTabs={showTabs} />

                        <h6>ورود با رمز عبور</h6>
                        <p className="otp-auth-sub">فقط برای حساب‌هایی که قبلاً ثبت‌نام کرده‌اند.</p>

                        {flashMessage}

                        <div
                            className={`otp-auth-field otp-auth-field--phone${passwordPhoneError ? ' otp-auth-field--error' : ''}`}
                        >
                            <span className="otp-auth-field-icon">
                                <Smartphone className="size-5" strokeWidth={1.75} />
                            </span>
                            <input
                                id="password-phone"
                                type="tel"
                                inputMode="numeric"
                                autoComplete="tel-national"
                                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                                dir="ltr"
                                value={passwordPhoneValue}
                                aria-invalid={Boolean(passwordPhoneError)}
                                onChange={(event) => {
                                    const next = normalizePhoneInput(event.target.value);
                                    passwordForm.setValue('phone', next, { shouldValidate: next.length === 11 });
                                }}
                                onBlur={() => passwordForm.trigger('phone')}
                            />
                        </div>
                        {passwordPhoneError && <p className="otp-auth-error">{passwordPhoneError}</p>}

                        <div
                            className={`otp-auth-field otp-auth-field--phone otp-auth-field--password${passwordError ? ' otp-auth-field--error' : ''}`}
                        >
                            <span className="otp-auth-field-icon">
                                <KeyRound className="size-5" strokeWidth={1.75} />
                            </span>
                            <input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                placeholder="رمز عبور"
                                aria-invalid={Boolean(passwordError)}
                                {...passwordForm.register('password')}
                            />
                        </div>
                        {passwordError && <p className="otp-auth-error">{passwordError}</p>}

                        <div className="otp-auth-actions">
                            <button
                                type="submit"
                                className="otp-auth-validate"
                                disabled={
                                    passwordForm.formState.isSubmitting ||
                                    passwordPhoneValue.length < 11 ||
                                    !passwordForm.watch('password')
                                }
                            >
                                ورود
                            </button>
                        </div>

                        <div className="otp-auth-footer">
                            <Link href="/" className="otp-auth-footer-link">
                                <ArrowRight className="size-4 shrink-0" aria-hidden />
                                بازگشت به صفحه اصلی
                            </Link>
                        </div>
                    </form>
                ) : (
                    <form className="otp-auth-card" onSubmit={step === 'phone' ? sendOtp : verifyOtp}>
                        <AuthIcon />
                        <AuthTabs tab={authTab} onChange={setAuthTab} showTabs={showTabs} />

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
                                        disabled={codeForm.formState.isSubmitting}
                                    />
                                </div>
                                {codeError && <p className="otp-auth-error">{codeError}</p>}

                                <div className="otp-auth-actions">
                                    <button
                                        type="submit"
                                        className="otp-auth-validate"
                                        disabled={codeForm.formState.isSubmitting || otpCode.length < 5}
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
                )}
            </div>
        </div>
    );
}
