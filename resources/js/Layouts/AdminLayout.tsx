import { DashboardShell } from '@/Components/Admin/shared/layout/dashboard-shell';
import { FlashMessages } from '@/Components/FlashMessages';
import { PropsWithChildren } from 'react';

export default function AdminLayout({ children }: PropsWithChildren) {
    return (
        <DashboardShell>
            <FlashMessages />
            {children}
        </DashboardShell>
    );
}
