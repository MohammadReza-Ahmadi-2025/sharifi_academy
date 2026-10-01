import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';

const DESKTOP_BREAKPOINT = 1024;

type SidebarContextType = {
    isDesktop: boolean;
    isMobileOpen: boolean;
    isCollapsed: boolean;
    toggleSidebar: () => void;
    closeMobileSidebar: () => void;
};

const SidebarContext = createContext<SidebarContextType | undefined>(undefined);

export function SidebarProvider({ children }: { children: ReactNode }) {
    const [isDesktop, setIsDesktop] = useState(false);
    const [isMobileOpen, setIsMobileOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia(`(min-width: ${DESKTOP_BREAKPOINT}px)`);

        const handleChange = () => {
            const desktop = mediaQuery.matches;
            setIsDesktop(desktop);

            if (desktop) {
                setIsMobileOpen(false);
            }
        };

        handleChange();
        mediaQuery.addEventListener('change', handleChange);

        return () => mediaQuery.removeEventListener('change', handleChange);
    }, []);

    const toggleSidebar = () => {
        if (isDesktop) {
            setIsCollapsed((prev) => !prev);
        } else {
            setIsMobileOpen((prev) => !prev);
        }
    };

    const closeMobileSidebar = () => {
        setIsMobileOpen(false);
    };

    return (
        <SidebarContext.Provider
            value={{
                isDesktop,
                isMobileOpen,
                isCollapsed,
                toggleSidebar,
                closeMobileSidebar,
            }}
        >
            {children}
        </SidebarContext.Provider>
    );
}

export function useSidebar() {
    const context = useContext(SidebarContext);

    if (!context) {
        throw new Error('useSidebar must be used within a SidebarProvider');
    }

    return context;
}
