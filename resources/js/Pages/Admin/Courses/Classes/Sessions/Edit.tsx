import { SessionFormFields } from '@/Components/Admin/Courses/SessionFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    course: { id: number };
    courseClass: { id: number };
    session: Record<string, unknown>;
    statusOptions: { value: string; label: string }[];
    adobeRooms: { id: number; sco_id: string }[];
    adobeRecordings: { id: number; name: string | null; sco_id: string }[];
    abilities: {
        adobeIndex: boolean;
        adobeCreate: boolean;
        adobeDelete: boolean;
        recordingIndex: boolean;
        recordingCreate: boolean;
        recordingDelete: boolean;
    };
};

export default function SessionsEdit({ course, courseClass, session, statusOptions, adobeRooms, adobeRecordings, abilities }: Props) {
    const base = `/admin/courses/${course.id}/classes/${courseClass.id}/sessions/${session.id}`;
    const form = useForm({
        session_number: Number(session.session_number ?? 1),
        title: String(session.title ?? ''),
        description: String(session.description ?? ''),
        status: String(session.status ?? 'scheduled'),
        scheduled_start_at: String(session.scheduled_start_at ?? ''),
        scheduled_end_at: String(session.scheduled_end_at ?? ''),
        actual_start_at: String(session.actual_start_at ?? ''),
        actual_end_at: String(session.actual_end_at ?? ''),
        timezone: String(session.timezone ?? 'Asia/Tehran'),
        cancelled_at: String(session.cancelled_at ?? ''),
        cancellation_reason: String(session.cancellation_reason ?? ''),
    });
    const roomForm = useForm({ sco_id: '', url_path: '', meeting_url: '', access_type: 'protected', passcode: '', status: 'active' });
    const recForm = useForm({ sco_id: '', name: '', playback_url: '', duration_seconds: 0, is_public: true });

    return (
        <AdminLayout>
            <Head title="ویرایش جلسه" />
            <PageHeader title="ویرایش جلسه" backHref={`/admin/courses/${course.id}/classes/${courseClass.id}/sessions`} />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.put(base); }}>
                <SessionFormFields
                    data={form.data}
                    errors={form.errors}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit">به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
            {(abilities.adobeIndex || abilities.adobeCreate || abilities.adobeDelete) && (
                <section className="mt-8">
                    <h3 className="font-medium">اتاق جلسه</h3>
                    {abilities.adobeCreate && (
                        <form onSubmit={(e) => { e.preventDefault(); roomForm.post(`${base}/adobe-rooms`); }} className="mt-2 flex flex-wrap gap-2">
                            <input placeholder="SCO" className="rounded border px-2" value={roomForm.data.sco_id} onChange={(e) => roomForm.setData('sco_id', e.target.value)} />
                            <input placeholder="Path" className="rounded border px-2" value={roomForm.data.url_path} onChange={(e) => roomForm.setData('url_path', e.target.value)} />
                            <input placeholder="URL" className="rounded border px-2" value={roomForm.data.meeting_url} onChange={(e) => roomForm.setData('meeting_url', e.target.value)} />
                            <AdminButton type="submit" size="sm">افزودن</AdminButton>
                        </form>
                    )}
                    {abilities.adobeIndex && (
                        <ul className="mt-2 text-sm">{adobeRooms.map((r) => <li key={r.id}>{r.sco_id}</li>)}</ul>
                    )}
                </section>
            )}
            {(abilities.recordingIndex || abilities.recordingCreate || abilities.recordingDelete) && (
                <section className="mt-8">
                    <h3 className="font-medium">ضبط‌ها</h3>
                    {abilities.recordingCreate && (
                        <form onSubmit={(e) => { e.preventDefault(); recForm.post(`${base}/adobe-recordings`); }} className="mt-2 flex flex-wrap gap-2">
                            <input placeholder="SCO" className="rounded border px-2" value={recForm.data.sco_id} onChange={(e) => recForm.setData('sco_id', e.target.value)} />
                            <input placeholder="Playback URL" className="rounded border px-2" value={recForm.data.playback_url} onChange={(e) => recForm.setData('playback_url', e.target.value)} />
                            <AdminButton type="submit" size="sm">افزودن ضبط</AdminButton>
                        </form>
                    )}
                    {abilities.recordingIndex && (
                        <ul className="mt-2 text-sm">{adobeRecordings.map((r) => <li key={r.id}>{r.name ?? r.sco_id}</li>)}</ul>
                    )}
                </section>
            )}
        </AdminLayout>
    );
}
