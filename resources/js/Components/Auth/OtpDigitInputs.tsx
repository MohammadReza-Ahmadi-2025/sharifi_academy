import { normalizeOtpDigit, toWesternDigits } from '@/lib/utils';
import { useCallback, useEffect, useRef } from 'react';

const LENGTH = 6;

type Props = {
    value: string;
    onChange: (code: string) => void;
    disabled?: boolean;
    id?: string;
};

export function OtpDigitInputs({ value, onChange, disabled, id = 'otp' }: Props) {
    const inputsRef = useRef<Array<HTMLInputElement | null>>([]);
    const digits = value.padEnd(LENGTH, ' ').slice(0, LENGTH).split('');

    const emit = useCallback(
        (nextDigits: string[]) => {
            onChange(nextDigits.join('').replace(/\s/g, ''));
        },
        [onChange],
    );

    useEffect(() => {
        inputsRef.current[0]?.focus();
    }, []);

    const focusAt = (index: number) => {
        const input = inputsRef.current[index];
        if (input) {
            input.focus();
            input.select();
        }
    };

    const handleChange = (index: number, raw: string) => {
        const digit = normalizeOtpDigit(raw);
        const next = [...digits];
        next[index] = digit || ' ';
        emit(next);

        if (digit && index < LENGTH - 1) {
            focusAt(index + 1);
        }
    };

    const handleKeyDown = (index: number, event: React.KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Backspace') {
            event.preventDefault();
            const next = [...digits];

            if (digits[index]?.trim()) {
                next[index] = ' ';
                emit(next);
                return;
            }

            if (index > 0) {
                next[index - 1] = ' ';
                emit(next);
                focusAt(index - 1);
            }
        }

        if (event.key === 'ArrowLeft' && index > 0) {
            event.preventDefault();
            focusAt(index - 1);
        }

        if (event.key === 'ArrowRight' && index < LENGTH - 1) {
            event.preventDefault();
            focusAt(index + 1);
        }
    };

    const handlePaste = (event: React.ClipboardEvent) => {
        event.preventDefault();
        const pasted = toWesternDigits(event.clipboardData.getData('text')).replace(/\D/g, '').slice(0, LENGTH);
        if (!pasted) {
            return;
        }

        const next = Array.from({ length: LENGTH }, (_, i) => pasted[i] ?? ' ');
        emit(next);
        focusAt(Math.min(pasted.length, LENGTH - 1));
    };

    return (
        <div id={id} className="otp-auth-inputs" dir="ltr">
            {digits.map((digit, index) => (
                <input
                    key={index}
                    ref={(el) => {
                        inputsRef.current[index] = el;
                    }}
                    type="text"
                    inputMode="numeric"
                    autoComplete={index === 0 ? 'one-time-code' : 'off'}
                    maxLength={1}
                    disabled={disabled}
                    className={`otp-auth-digit${digit.trim() ? ' otp-auth-digit--filled' : ''}`}
                    value={digit.trim()}
                    aria-label={`رقم ${index + 1}`}
                    onChange={(event) => handleChange(index, event.target.value)}
                    onKeyDown={(event) => handleKeyDown(index, event)}
                    onPaste={handlePaste}
                    onFocus={(event) => event.target.select()}
                />
            ))}
        </div>
    );
}
