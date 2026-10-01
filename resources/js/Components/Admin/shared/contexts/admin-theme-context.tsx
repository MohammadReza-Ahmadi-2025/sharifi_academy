import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';

const STORAGE_KEY = 'sharifi-admin-theme';

type AdminThemeContextType = {
    isDark: boolean;
    toggleTheme: () => void;
    mounted: boolean;
};

const AdminThemeContext = createContext<AdminThemeContextType | undefined>(undefined);

function applyDocumentTheme(isDark: boolean) {
    document.documentElement.classList.toggle('dark', isDark);

    if (isDark) {
        document.documentElement.dataset.adminTheme = 'dark';
    } else {
        delete document.documentElement.dataset.adminTheme;
    }
}

export function AdminThemeProvider({ children }: { children: ReactNode }) {
    const [isDark, setIsDark] = useState(false);
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        const stored = localStorage.getItem(STORAGE_KEY);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initial = stored === 'dark' || (stored === null && prefersDark);
        setIsDark(initial);
        applyDocumentTheme(initial);
        setMounted(true);

        return () => {
            document.documentElement.classList.remove('dark');
            delete document.documentElement.dataset.adminTheme;
        };
    }, []);

    useEffect(() => {
        if (!mounted) {
            return;
        }

        applyDocumentTheme(isDark);
    }, [isDark, mounted]);

    const toggleTheme = () => {
        setIsDark((prev) => {
            const next = !prev;
            localStorage.setItem(STORAGE_KEY, next ? 'dark' : 'light');

            return next;
        });
    };

    return (
        <AdminThemeContext.Provider value={{ isDark, toggleTheme, mounted }}>
            {children}
        </AdminThemeContext.Provider>
    );
}

export function useAdminTheme() {
    const context = useContext(AdminThemeContext);

    if (!context) {
        throw new Error('useAdminTheme must be used within AdminThemeProvider');
    }

    return context;
}
