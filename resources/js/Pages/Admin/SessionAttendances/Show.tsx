import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { findSelectOption } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useState } from 'react';

type Row = {
    user_id: number;
    name: string;
    mobile: string;
    status: string;
    duration_minutes?: number;
    source_label?: string | null;
    note: string;
};

type Props = {
    session: { id: number; title: string; session_number: number; course_title: string | null; class_title: string | null };
    rows: Row[];
    statusOptions: { value: string; label: string }[];
    can_sync_adobe?: boolean;
    abilities: { update: boolean };
};

export default function SessionAttendanceShow({ session, rows, statusOptions, can_sync_adobe = false, abilities }: Props) {
    const form = useForm({ attendances: rows });
    const [syncing, setSyncing] = useState(false);
    const statusSelectOptions = statusOptions.map((o) => ({ value: o.value, label: o.label }));

    const syncAdobe = () => {
        setSyncing(true);
        router.post(`/admin/session-attendances/${session.id}/sync-adobe`, { mark_absent: true }, {
            preserveScroll: true,
            onFinish: () => setSyncing(false),
        });
    };

    return (
        <AdminLayout>
            <Head title={`حضور — ${session.title}`} />
            <PageHeader
                title={session.title}
                description={`${session.course_title ?? ''} · ${session.class_title ?? ''}`}
                backHref="/admin/session-attendances"
                actions={
                    can_sync_adobe ? (
                        <AdminButton type="button" variant="outline" disabled={syncing} onClick={syncAdobe}>
                            <RefreshCw className={`ml-1 h-4 w-4 ${syncing ? 'animate-spin' : ''}`} />
                            همگام‌سازی از Adobe
                        </AdminButton>
                    ) : null
                }
            />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    if (abilities.update) form.put(`/admin/session-attendances/${session.id}`);
                }}
                className="mt-6 space-y-4"
            >
                <Card className="divide-y overflow-hidden">
                    {form.data.attendances.map((row, index) => (
                        <div key={row.user_id} className="grid gap-3 p-4 md:grid-cols-4">
                            <div>
                                <div className="font-medium">{row.name}</div>
                                <div className="text-sm text-slate-500" dir="ltr">{row.mobile}</div>
                                {(row.duration_minutes ?? 0) > 0 ? (
                                    <div className="mt-1 text-xs text-slate-500">{row.duration_minutes} دقیقه در Adobe</div>
                                ) : null}
                                {row.source_label ? (
                                    <div className="mt-0.5 text-xs text-slate-400">{row.source_label}</div>
                                ) : null}
                            </div>
                            <FormField label="وضعیت">
                                <Select
                                    isDisabled={!abilities.update}
                                    options={statusSelectOptions}
                                    value={findSelectOption(statusSelectOptions, row.status)}
                                    onChange={(opt) => {
                                        const next = [...form.data.attendances];
                                        next[index] = { ...row, status: opt?.value ?? row.status };
                                        form.setData('attendances', next);
                                    }}
                                />
                            </FormField>
                            <FormField label="یادداشت" className="md:col-span-2">
                                <Input
                                    value={row.note}
                                    disabled={!abilities.update}
                                    onChange={(e) => {
                                        const next = [...form.data.attendances];
                                        next[index] = { ...row, note: e.target.value };
                                        form.setData('attendances', next);
                                    }}
                                />
                            </FormField>
                        </div>
                    ))}
                    {form.data.attendances.length === 0 && (
                        <p className="p-6 text-sm text-slate-500">دانشجوی فعالی برای این کلاس ثبت نشده است.</p>
                    )}
                </Card>
                {abilities.update && form.data.attendances.length > 0 && (
                    <AdminButton type="submit" disabled={form.processing}>ذخیره حضور و غیاب</AdminButton>
                )}
            </form>
        </AdminLayout>
    );
}
