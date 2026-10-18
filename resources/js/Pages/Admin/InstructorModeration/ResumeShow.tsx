import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import type { InstructorResumeProfile } from '@/types/instructor-resume';
import { Head, router, useForm } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { FormEvent } from 'react';

type Props = {
    profile: InstructorResumeProfile;
    moderation: {
        status: string;
        status_label: string;
        note: string | null;
        submitted_at: string | null;
    };
    abilities: { moderate: boolean };
};

export default function InstructorModerationResumeShow({ profile, moderation, abilities }: Props) {
    const rejectForm = useForm({ note: '' });

    const approve = () => {
        router.post(`/admin/instructor-moderation/resumes/${profile.id}/approve`, {}, { preserveScroll: true });
    };

    const reject = (e: FormEvent) => {
        e.preventDefault();
        rejectForm.post(`/admin/instructor-moderation/resumes/${profile.id}/reject`, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <Head title={`بررسی رزومه ${profile.fullName}`} />
            <PageHeader
                title={`بررسی رزومه: ${profile.fullName}`}
                description={`وضعیت: ${moderation.status_label}`}
                backHref="/admin/instructor-moderation"
            />

            {moderation.note ? (
                <Card className="mb-4 border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    یادداشت قبلی: {moderation.note}
                </Card>
            ) : null}

            <Card className="mb-6 p-6">
                <InstructorResumeProfileView profile={profile} variant="page" />
            </Card>

            {abilities.moderate ? (
                <Card className="p-4">
                    <div className="flex flex-wrap gap-3">
                        <AdminButton type="button" onClick={approve}>
                            <Check className="size-4" />
                            تایید و انتشار عمومی
                        </AdminButton>
                    </div>
                    <form onSubmit={reject} className="mt-4 space-y-3 border-t border-slate-200 pt-4">
                        <label className="block text-sm">
                            <span className="mb-1 block font-medium text-slate-700">دلیل رد (اختیاری)</span>
                            <textarea
                                className="min-h-20 w-full rounded-lg border px-3 py-2 text-sm"
                                value={rejectForm.data.note}
                                onChange={(e) => rejectForm.setData('note', e.target.value)}
                            />
                        </label>
                        <AdminButton type="submit" variant="outline" disabled={rejectForm.processing}>
                            <X className="size-4" />
                            رد رزومه
                        </AdminButton>
                    </form>
                </Card>
            ) : null}
        </AdminLayout>
    );
}
