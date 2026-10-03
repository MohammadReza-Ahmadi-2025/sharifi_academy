import { DashboardShell } from '@/Components/Admin/shared/layout/dashboard-shell';
import { PropsWithChildren } from 'react';

export default function AdminLayout({ children }: PropsWithChildren) {
    return <DashboardShell>{children}</DashboardShell>;
}
