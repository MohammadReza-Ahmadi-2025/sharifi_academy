import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

type KeyValue = { field: string; field_label: string; value: string };
type Change = { field: string; field_label: string; from: string; to: string };

type Props = {
    log: {
        id: number;
        description: string;
        action_label: string;
        module_label: string;
        route_name: string | null;
        ip_address: string | null;
        user_agent: string | null;
        user: { id: number; name: string; mobile: string } | null;
        created_at: string | null;
        changes: Change[];
        snapshot: KeyValue[];
        before: KeyValue[];
        after: KeyValue[];
    };
};

function KeyValueTable({ rows, valueHeader = 'مقدار' }: { rows: KeyValue[]; valueHeader?: string }) {
    if (rows.length === 0) {
        return <p className="text-sm text-slate-500">—</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>فیلد</TableHead>
                    <TableHead>{valueHeader}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {rows.map((row) => (
                    <TableRow key={row.field}>
                        <TableCell className="font-medium">{row.field_label}</TableCell>
                        <TableCell className="break-all font-mono text-sm" dir="auto">{row.value}</TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

export default function ActivityLogShow({ log }: Props) {
    const hasChanges = log.changes.length > 0;

    return (
        <AdminLayout>
            <Head title={`گزارش #${log.id}`} />
            <PageHeader title={log.description} backHref="/admin/activity-logs" />
            <div className="mt-6 space-y-4">
                <Card className="space-y-3 p-6">
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge>{log.action_label}</Badge>
                        <Badge variant="secondary">{log.module_label}</Badge>
                    </div>
                    {log.user && (
                        <p className="text-sm">
                            <span className="text-slate-500">کاربر: </span>
                            {log.user.name} ({log.user.mobile})
                        </p>
                    )}
                    {log.route_name && (
                        <p className="text-sm font-mono text-slate-600" dir="ltr">{log.route_name}</p>
                    )}
                    {log.ip_address && (
                        <p className="text-sm text-slate-600" dir="ltr">IP: {log.ip_address}</p>
                    )}
                </Card>

                {hasChanges && (
                    <Card className="overflow-hidden p-0">
                        <div className="border-b px-6 py-4 font-semibold">تغییرات (قبل → بعد)</div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>فیلد</TableHead>
                                    <TableHead>قبل</TableHead>
                                    <TableHead>بعد</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {log.changes.map((row) => (
                                    <TableRow key={row.field}>
                                        <TableCell className="font-medium">{row.field_label}</TableCell>
                                        <TableCell className="break-all text-sm text-red-700 dark:text-red-400">
                                            {row.from}
                                        </TableCell>
                                        <TableCell className="break-all text-sm text-emerald-700 dark:text-emerald-400">
                                            {row.to}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                {!hasChanges && log.snapshot.length > 0 && (
                    <Card className="overflow-hidden p-0">
                        <div className="border-b px-6 py-4 font-semibold">اسنپ‌شات کامل</div>
                        <div className="p-4">
                            <KeyValueTable rows={log.snapshot} />
                        </div>
                    </Card>
                )}

                {log.before.length > 0 && log.after.length > 0 && !hasChanges && (
                    <div className="grid gap-4 lg:grid-cols-2">
                        <Card className="overflow-hidden p-0">
                            <div className="border-b px-6 py-4 font-semibold">قبل</div>
                            <div className="p-4">
                                <KeyValueTable rows={log.before} />
                            </div>
                        </Card>
                        <Card className="overflow-hidden p-0">
                            <div className="border-b px-6 py-4 font-semibold">بعد</div>
                            <div className="p-4">
                                <KeyValueTable rows={log.after} />
                            </div>
                        </Card>
                    </div>
                )}

                <AdminButton variant="outline" href="/admin/activity-logs">بازگشت به لیست</AdminButton>
            </div>
        </AdminLayout>
    );
}
