import { useCallback, useEffect, useState } from 'react';

const HEALTH_POLL_INTERVAL_MS = 30_000;

export type SystemHealthState = 'loading' | 'online' | 'offline';

export function useSystemHealth() {
    const [state, setState] = useState<SystemHealthState>('loading');

    const checkHealth = useCallback(async () => {
        try {
            const response = await fetch('/up', { method: 'GET', credentials: 'same-origin' });
            setState(response.ok ? 'online' : 'offline');
        } catch {
            setState('offline');
        }
    }, []);

    useEffect(() => {
        void checkHealth();

        const intervalId = window.setInterval(() => {
            void checkHealth();
        }, HEALTH_POLL_INTERVAL_MS);

        return () => window.clearInterval(intervalId);
    }, [checkHealth]);

    return { state };
}
