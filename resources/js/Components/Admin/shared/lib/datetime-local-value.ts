/** مقدار `datetime-local` یا `Y-m-d\TH:i` — فقط تاریخ و ساعت (بدون ثانیه/zone). */
export function splitIsoDateTime(value: string): { date: string; time: string } {
    if (!value) {
        return { date: '', time: '' };
    }

    const normalized = value.includes('T') ? value : value.replace(' ', 'T');
    const [datePart, timePart] = normalized.split('T');
    const time = timePart ? timePart.slice(0, 5) : '';

    return { date: datePart ?? '', time };
}

export function joinIsoDateTime(date: string, time: string): string {
    if (!date) {
        return '';
    }

    const safeTime = time && /^\d{2}:\d{2}$/.test(time) ? time : '00:00';

    return `${date}T${safeTime}`;
}

/** برای PersianDatePicker — از ISO کامل یا datetime-local. */
export function toGregorianDateOnly(value: string): string {
    if (!value) {
        return '';
    }

    return value.split('T')[0]?.slice(0, 10) ?? '';
}
