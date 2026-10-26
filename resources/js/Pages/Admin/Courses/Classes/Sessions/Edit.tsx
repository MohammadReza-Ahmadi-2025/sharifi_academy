import { SessionFormFields } from '@/Components/Admin/Courses/SessionFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    routePrefix: string;
    course: { id: number };
    courseClass: { id: number; online_platform?: string };
    session: Record<string, unknown>;
    statusOptions: { value: string; label: string }[];
    adobeRooms: { id: number; sco_id: string; url_path?: string; meeting_url?: string }[];
    adobeRecordings: {
        id: number;
        name: string | null;
        sco_id: string;
        playback_url?: string | null;
        download_url?: string | null;
    }[];
    abilities: {
        adobeIndex: boolean;
        adobeCreate: boolean;
        adobeDelete: boolean;
        recordingIndex: boolean;
        recordingCreate: boolean;
        recordingDelete: boolean;
        adobeSync: boolean;
        recordingSync: boolean;
    };
};

export default function SessionsEdit({ routePrefix, course, courseClass, session, statusOptions, adobeRooms, adobeRecordings, abilities }: Props) {
    const base = `${routePrefix}/courses/${course.id}/classes/${courseClass.id}/sessions/${session.id}`;
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
        video_url: String(session.video_url ?? ''),
    });
    const showVideoUrl = courseClass.online_platform === 'video_link';
    const showAdobePanels = courseClass.online_platform !== 'video_link';
    const roomForm = useForm({ sco_id: '', url_path: '', meeting_url: '', access_type: 'protected', passcode: '', status: 'active' });
    const recForm = useForm({ sco_id: '', name: '', playback_url: '', duration_seconds: 0, is_public: true });

    return (
        <AdminLayout>
            <Head title="ویرایش جلسه" />
            <PageHeader title="ویرایش جلسه" backHref={`${routePrefix}/courses/${course.id}/classes/${courseClass.id}/sessions`} />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.put(base); }}>
                <SessionFormFields
                    data={form.data}
                    errors={form.errors}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    showVideoUrl={showVideoUrl}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit">به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
            {showAdobePanels && (abilities.adobeIndex || abilities.adobeCreate || abilities.adobeDelete) && (
                <section className="mt-8">
                    <h3 className="font-medium">اتاق جلسه (Adobe Connect)</h3>
                    <p className="mt-1 text-sm text-slate-600">
                        فقط sco-id meeting را وارد کنید؛ با API از سرور مسیر و لینک ورود پر می‌شود (نیاز به .env).
                    </p>
                    {abilities.adobeCreate && (
                        <form onSubmit={(e) => { e.preventDefault(); roomForm.post(`${base}/adobe-rooms`); }} className="mt-2 flex flex-wrap gap-2">
                            <input placeholder="sco-id meeting" className="rounded border px-2" dir="ltr" value={roomForm.data.sco_id} onChange={(e) => roomForm.setData('sco_id', e.target.value)} />
                            <input placeholder="url-path (اختیاری)" className="rounded border px-2" dir="ltr" value={roomForm.data.url_path} onChange={(e) => roomForm.setData('url_path', e.target.value)} />
                            <AdminButton type="submit" size="sm">افزودن / سینک</AdminButton>
                        </form>
                    )}
                    {abilities.adobeIndex && (
                        <ul className="mt-2 space-y-2 text-sm">
                            {adobeRooms.map((r) => (
                                <li key={r.id} className="flex flex-wrap items-center gap-2 rounded border p-2">
                                    <span dir="ltr" className="font-mono">{r.sco_id}</span>
                                    {r.meeting_url ? (
                                        <a href={r.meeting_url} className="text-indigo-600 hover:underline" target="_blank" rel="noreferrer">
                                            لینک ورود
                                        </a>
                                    ) : null}
                                    {abilities.adobeSync ? (
                                        <AdminButton
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            onClick={() => router.post(`${base}/adobe-rooms/${r.id}/sync`)}
                                        >
                                            سینک از Adobe
                                        </AdminButton>
                                    ) : null}
                                    {abilities.adobeDelete ? (
                                        <AdminButton
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            aria-label="حذف اتاق"
                                            onClick={() => router.delete(`${base}/adobe-rooms/${r.id}`, { preserveScroll: true })}
                                        >
                                            <Trash2 className="size-4 text-rose-600" />
                                        </AdminButton>
                                    ) : null}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            )}
            {showAdobePanels && (abilities.recordingIndex || abilities.recordingCreate || abilities.recordingDelete) && (
                <section className="mt-8">
                    <h3 className="font-medium">ضبط‌ها</h3>
                    {abilities.recordingSync && (
                        <AdminButton
                            type="button"
                            className="mt-2"
                            variant="secondary"
                            onClick={() => router.post(`${base}/adobe-recordings/sync`)}
                        >
                            دریافت ضبط‌ها از Adobe Connect
                        </AdminButton>
                    )}
                    {abilities.recordingCreate && (
                        <form onSubmit={(e) => { e.preventDefault(); recForm.post(`${base}/adobe-recordings`); }} className="mt-2 flex flex-wrap gap-2">
                            <input placeholder="SCO" className="rounded border px-2" value={recForm.data.sco_id} onChange={(e) => recForm.setData('sco_id', e.target.value)} />
                            <input placeholder="Playback URL" className="rounded border px-2" value={recForm.data.playback_url} onChange={(e) => recForm.setData('playback_url', e.target.value)} />
                            <AdminButton type="submit" size="sm">افزودن ضبط</AdminButton>
                        </form>
                    )}
                    {abilities.recordingIndex && (
                        <ul className="mt-2 space-y-2 text-sm">
                            {adobeRecordings.map((r) => (
                                <li key={r.id} className="flex items-center gap-2 rounded border p-2">
                                    <span>{r.name ?? r.sco_id}</span>
                                    {r.playback_url ? (
                                        <a href={r.playback_url} target="_blank" rel="noreferrer" className="text-indigo-600 hover:underline">
                                            پخش
                                        </a>
                                    ) : null}
                                    {r.download_url ? (
                                        <a href={r.download_url} target="_blank" rel="noreferrer" className="text-indigo-600 hover:underline">
                                            دانلود MP4
                                        </a>
                                    ) : null}
                                    {abilities.recordingDelete ? (
                                        <AdminButton
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            aria-label="حذف ضبط"
                                            onClick={() => router.delete(`${base}/adobe-recordings/${r.id}`, { preserveScroll: true })}
                                        >
                                            <Trash2 className="size-4 text-rose-600" />
                                        </AdminButton>
                                    ) : null}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            )}
        </AdminLayout>
    );
}
