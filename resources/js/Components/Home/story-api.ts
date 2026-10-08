function xsrfToken(): string | null {
    const raw = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    return raw ? decodeURIComponent(raw) : null;
}

/** ثبت بازدید — درخواست JSON (نه Inertia) */
export function recordStoryView(storyId: number): void {
    const token = xsrfToken();

    void fetch(`/stories/${storyId}/views`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify({}),
    });
}
