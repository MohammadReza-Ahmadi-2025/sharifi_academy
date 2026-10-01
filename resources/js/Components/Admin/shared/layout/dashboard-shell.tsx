import { AdminThemeProvider, useAdminTheme } from '@/Components/Admin/shared/contexts/admin-theme-context';
import { SidebarProvider } from '@/Components/Admin/shared/contexts/sidebar-context';
import { Header } from '@/Components/Admin/shared/layout/header';
import { NavigationProgress } from '@/Components/Admin/shared/layout/navigation-progress';
import { PageContainer } from '@/Components/Admin/shared/layout/page-container';
import { Sidebar } from '@/Components/Admin/shared/layout/sidebar';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type DashboardShellProps = {
    children: ReactNode;
};

function DashboardShellInner({ children }: DashboardShellProps) {
    const { isDark, mounted } = useAdminTheme();

    return (
        <SidebarProvider>
            <NavigationProgress />
            <div
                className={cn(
                    'admin-dashboard flex min-h-screen bg-gray-50 text-gray-900 dark:bg-slate-950 dark:text-slate-100',
                    mounted && isDark && 'dark',
                )}
                dir="rtl"
            >
                <Sidebar />

                <div className="flex min-h-screen min-w-0 flex-1 flex-col">
                    <Header />
                    <main className="min-w-0 flex-1">
                        <PageContainer>{children}</PageContainer>
                    </main>
                </div>
            </div>
        </SidebarProvider>
    );
}

export function DashboardShell({ children }: DashboardShellProps) {
    return (
        <AdminThemeProvider>
            <DashboardShellInner>{children}</DashboardShellInner>
        </AdminThemeProvider>
    );
}
