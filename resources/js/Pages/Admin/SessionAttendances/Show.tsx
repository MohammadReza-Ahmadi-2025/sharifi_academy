import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { findSelectOption } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Row = { user_id: number; name: string; mobile: string; status: string; note: string };

export default function SessionAttendanceShow({ session, rows, statusOptions, abilities }: { session: { id: number; title: string; session_number: number; course_title: string | null; class_title: string | null }; rows: Row[]; statusOptions: { value: string; label: string }[]; abilities: { update: boolean } }) {
    const form = useForm({ attendances: rows });
    return (
        <AdminLayout>
            <Head title={`حضور — ${session.title}`} />
            <PageHeader title={session.title} description={`${session.course_title ?? ''} · ${session.class_title ?? ''}`} backHref="/admin/session-attendances" />
            <form onSubmit={(e) => { e.preventDefault(); if (abilities.update) form.put(`/admin/session-attendances/${session.id}`); }} className="mt-6 space-y-4">
                <Card className="divide-y overflow-hidden">
                    {form.data.attendances.map((row, index) => (
                        <div key={row.user_id} className="grid gap-3 p-4 md:grid-cols-3">
                            <div><div className="font-medium">{row.name}</div><div className="text-sm text-slate-500" dir="ltr">{row.mobile}</div></div>
                            <FormField label="وضعیت">
                                <Select
                                    isDisabled={!abilities.update}
                                    options={statusOptions.map((o) => ({ value: o.value, label: o.label }))}
                                    value={findSelectOption(statusOptions.map((o) => ({ value: o.value, label: o.label })), row.status)}
                                    onChange={(opt) => {
                                        const next = [...form.data.attendances];
                                        next[index] = { ...row, status: opt?.value ?? row.status };
                                        form.setData('attendances', next);
                                    }}
                                />
                            </FormField>
                            <FormField label="یادداشت"><Input value={row.note} disabled={!abilities.update} onChange={(e) => { const next = [...form.data.attendances]; next[index] = { ...row, note: e.target.value }; form.setData('attendances', next); }} /></FormField>
                        </div>
                    ))}
                    {form.data.attendances.length === 0 && <p className="p-6 text-sm text-slate-500">دانشجوی فعالی برای این کلاس ثبت نشده است.</p>}
                </Card>
                {abilities.update && form.data.attendances.length > 0 && <AdminButton type="submit" disabled={form.processing}>ذخیره حضور و غیاب</AdminButton>}
            </form>
        </AdminLayout>
    );
}
