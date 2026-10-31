import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { withResumeAttachmentsPayload } from '@/Components/Profile/resume-form-payload';
import { FileUploader, useFileUploadState } from '@/Components/shared/FileUploader';
import { formatFileSize } from '@/Components/shared/file-upload';
import type { InstructorResumeProfile, ResumeEntry, ResumeSection } from '@/types/instructor-resume';
import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ExternalLink, Pencil, Plus, Trash2, X } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';

type Props = {
    items: ResumeEntry[];
    sections: ResumeSection[];
    profilePreview: InstructorResumeProfile;
    typeOptions: { value: string; label: string }[];
    attachmentLimits: { max_files: number; max_kilobytes: number };
    moderation: {
        status: string;
        status_label: string;
        note: string | null;
        can_submit: boolean;
    };
    skillOptions: { id: number; name: string }[];
    selectedSkills: { skill_id: number; level: number | null }[];
};

const emptyForm = {
    type: 'experience',
    title: '',
    organization: '',
    start_date: '',
    end_date: '',
    is_current: false,
    link: '',
    description: '',
    display_order: 0,
};

export default function InstructorResumeIndex({
    items,
    sections,
    profilePreview,
    typeOptions,
    attachmentLimits,
    moderation,
    skillOptions,
    selectedSkills,
}: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };
    const [editingId, setEditingId] = useState<number | null>(null);
    const [formOpen, setFormOpen] = useState(false);
    const [removeAttachmentIds, setRemoveAttachmentIds] = useState<number[]>([]);
    const [newAttachments, setNewAttachments] = useFileUploadState();

    const form = useForm(emptyForm);

    const initialSkillMap = useMemo(() => {
        const map: Record<number, number | ''> = {};
        for (const row of selectedSkills) {
            map[row.skill_id] = row.level ?? '';
        }
        return map;
    }, [selectedSkills]);

    const [skillLevels, setSkillLevels] = useState<Record<number, number | ''>>(initialSkillMap);

    const toggleSkill = (skillId: number) => {
        setSkillLevels((prev) => {
            const next = { ...prev };
            if (skillId in next) {
                delete next[skillId];
            } else {
                next[skillId] = 3;
            }
            return next;
        });
    };

    const saveSkills = () => {
        const skills = Object.entries(skillLevels).map(([skillId, level]) => ({
            skill_id: Number(skillId),
            level: level === '' ? null : Number(level),
        }));
        router.put('/instructor/resume/skills', { skills }, { preserveScroll: true });
    };

    const editingItem = useMemo(
        () => (editingId ? items.find((i) => i.id === editingId) : null),
        [editingId, items],
    );

    const existingAttachments = editingItem?.attachments ?? [];
    const keptExistingCount =
        existingAttachments.filter((a) => !removeAttachmentIds.includes(a.id)).length + newAttachments.length;

    const startCreate = (type?: string) => {
        setEditingId(null);
        setRemoveAttachmentIds([]);
        setNewAttachments([]);
        form.clearErrors();
        form.setData({ ...emptyForm, type: type ?? 'experience' });
        setFormOpen(true);
    };

    const startEdit = (item: ResumeEntry) => {
        setEditingId(item.id);
        setRemoveAttachmentIds([]);
        setNewAttachments([]);
        form.clearErrors();
        form.setData({
            type: item.type,
            title: item.title,
            organization: item.organization ?? '',
            start_date: item.start_date ?? '',
            end_date: item.end_date ?? '',
            is_current: item.is_current ?? false,
            link: item.link ?? '',
            description: item.description ?? '',
            display_order: item.display_order ?? 0,
        });
        setFormOpen(true);
    };

    const resetForm = () => {
        setEditingId(null);
        setFormOpen(false);
        setRemoveAttachmentIds([]);
        setNewAttachments([]);
        form.reset();
    };

    const toggleRemoveAttachment = (id: number) => {
        setRemoveAttachmentIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) =>
            withResumeAttachmentsPayload(data, newAttachments, removeAttachmentIds),
        );

        const options = { forceFormData: true, onSuccess: resetForm, preserveScroll: true };

        if (editingId) {
            form.put(`/instructor/resume/${editingId}`, options);
        } else {
            form.post('/instructor/resume', options);
        }
    };

    const profileForPreview: InstructorResumeProfile = {
        ...profilePreview,
        sections,
    };

    return (
        <InstructorLayout
            title="رزومه من"
            description="سوابق تحصیلی، شغلی و پیوست‌ها — مشابه پروفایل لینکدین"
            contentVariant="plain"
        >
            <Head title="رزومه من" />

            {flash?.success ? (
                <p className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                    {flash.success}
                </p>
            ) : null}

            <div
                className={`mb-4 rounded-xl border px-4 py-3 text-sm ${
                    moderation.status === 'approved'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                        : moderation.status === 'pending'
                          ? 'border-amber-200 bg-amber-50 text-amber-900'
                          : moderation.status === 'rejected'
                            ? 'border-red-200 bg-red-50 text-red-900'
                            : 'border-slate-200 bg-slate-50 text-slate-700'
                }`}
            >
                <p className="font-medium">وضعیت انتشار عمومی: {moderation.status_label}</p>
                {moderation.note ? <p className="mt-1 text-xs opacity-90">توضیح ادمین: {moderation.note}</p> : null}
                {moderation.can_submit ? (
                    <button
                        type="button"
                        className="mt-3 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                        onClick={() => router.post('/instructor/resume/submit')}
                    >
                        ارسال برای تایید ادمین
                    </button>
                ) : null}
                {moderation.status === 'pending' ? (
                    <p className="mt-2 text-xs">پس از بررسی ادمین، رزومه در سایت و صفحه اصلی نمایش داده می‌شود.</p>
                ) : null}
            </div>

            {skillOptions.length > 0 ? (
                <div className="mb-6 rounded-xl border border-slate-200 bg-white p-4">
                    <h3 className="text-sm font-semibold text-slate-800">مهارت‌ها</h3>
                    <p className="mt-1 text-xs text-slate-500">مهارت‌های تأیید‌شده در رزومه عمومی نمایش داده می‌شوند.</p>
                    <ul className="mt-3 space-y-2">
                        {skillOptions.map((skill) => {
                            const selected = skill.id in skillLevels;

                            return (
                                <li key={skill.id} className="flex flex-wrap items-center gap-2 text-sm">
                                    <label className="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            checked={selected}
                                            onChange={() => toggleSkill(skill.id)}
                                        />
                                        {skill.name}
                                    </label>
                                    {selected ? (
                                        <select
                                            value={skillLevels[skill.id] === '' ? '' : String(skillLevels[skill.id])}
                                            onChange={(e) =>
                                                setSkillLevels((prev) => ({
                                                    ...prev,
                                                    [skill.id]: e.target.value === '' ? '' : Number(e.target.value),
                                                }))
                                            }
                                            className="rounded border border-slate-200 px-2 py-1 text-xs"
                                        >
                                            <option value="">بدون سطح</option>
                                            {[1, 2, 3, 4, 5].map((level) => (
                                                <option key={level} value={level}>
                                                    سطح {level}
                                                </option>
                                            ))}
                                        </select>
                                    ) : null}
                                </li>
                            );
                        })}
                    </ul>
                    <button
                        type="button"
                        onClick={saveSkills}
                        className="mt-3 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                    >
                        ذخیره مهارت‌ها
                    </button>
                </div>
            ) : null}

            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p className="text-sm text-slate-600">
                    نمایش عمومی برای دانشجویان و صفحه اصلی
                </p>
                {profilePreview.profile_url && moderation.status === 'approved' ? (
                    <Link
                        href={profilePreview.profile_url}
                        target="_blank"
                        className="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-50"
                    >
                        پیش‌نمایش عمومی
                        <ExternalLink className="size-4" aria-hidden />
                    </Link>
                ) : null}
            </div>

            <div className="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <InstructorResumeProfileView profile={profileForPreview} variant="compact" />
            </div>

            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 className="text-base font-semibold text-slate-900">مدیریت بخش‌ها</h2>
                <button
                    type="button"
                    onClick={() => startCreate()}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    <Plus className="size-4" aria-hidden />
                    افزودن مورد
                </button>
            </div>

            <div className="space-y-8">
                {typeOptions.map((typeOpt) => {
                    const section = sections.find((s) => s.type === typeOpt.value);
                    const sectionItems = section?.items ?? [];

                    return (
                        <section key={typeOpt.value} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                                <h3 className="font-semibold text-slate-900">{typeOpt.label}</h3>
                                <button
                                    type="button"
                                    onClick={() => startCreate(typeOpt.value)}
                                    className="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                                >
                                    + افزودن
                                </button>
                            </div>
                            {sectionItems.length === 0 ? (
                                <p className="text-sm text-slate-500">موردی ثبت نشده است.</p>
                            ) : (
                                <ul className="space-y-3">
                                    {sectionItems.map((item) => (
                                        <li
                                            key={item.id}
                                            className="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3"
                                        >
                                            <div className="min-w-0 text-sm">
                                                <p className="font-semibold text-slate-900">{item.title}</p>
                                                {item.organization ? (
                                                    <p className="text-slate-600">{item.organization}</p>
                                                ) : null}
                                                {item.period ? (
                                                    <p className="mt-0.5 text-xs text-slate-400">{item.period}</p>
                                                ) : null}
                                                {item.attachments && item.attachments.length > 0 ? (
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        {item.attachments.length.toLocaleString('fa-IR')} پیوست
                                                    </p>
                                                ) : null}
                                            </div>
                                            <div className="flex shrink-0 gap-1">
                                                <button
                                                    type="button"
                                                    className="rounded-lg p-2 text-indigo-600 hover:bg-indigo-50"
                                                    aria-label="ویرایش"
                                                    onClick={() => startEdit(item)}
                                                >
                                                    <Pencil className="size-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    className="rounded-lg p-2 text-red-600 hover:bg-red-50"
                                                    aria-label="حذف"
                                                    onClick={() => router.delete(`/instructor/resume/${item.id}`)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </section>
                    );
                })}
            </div>

            {formOpen ? (
                <div className="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 sm:items-center">
                    <div
                        className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-xl"
                        role="dialog"
                        aria-labelledby="resume-form-title"
                    >
                        <div className="mb-4 flex items-center justify-between gap-2">
                            <h2 id="resume-form-title" className="text-lg font-semibold">
                                {editingId ? 'ویرایش مورد رزومه' : 'افزودن به رزومه'}
                            </h2>
                            <button type="button" onClick={resetForm} className="rounded-lg p-1 text-slate-500 hover:bg-slate-100">
                                <X className="size-5" />
                            </button>
                        </div>

                        <form onSubmit={submit} className="space-y-4">
                            <label className="block text-sm">
                                <span className="mb-1 block text-slate-600">بخش</span>
                                <select
                                    className="w-full rounded-lg border px-3 py-2"
                                    value={form.data.type}
                                    onChange={(e) => form.setData('type', e.target.value)}
                                >
                                    {typeOptions.map((opt) => (
                                        <option key={opt.value} value={opt.value}>{opt.label}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm">
                                <span className="mb-1 block text-slate-600">عنوان *</span>
                                <input
                                    required
                                    className="w-full rounded-lg border px-3 py-2"
                                    value={form.data.title}
                                    onChange={(e) => form.setData('title', e.target.value)}
                                />
                            </label>
                            <label className="block text-sm">
                                <span className="mb-1 block text-slate-600">موسسه / شرکت / دانشگاه</span>
                                <input
                                    className="w-full rounded-lg border px-3 py-2"
                                    value={form.data.organization}
                                    onChange={(e) => form.setData('organization', e.target.value)}
                                />
                            </label>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <label className="block text-sm">
                                    <span className="mb-1 block text-slate-600">شروع</span>
                                    <PersianDatePicker
                                        value={form.data.start_date}
                                        onChange={(v) => form.setData('start_date', v)}
                                    />
                                </label>
                                <label className="block text-sm">
                                    <span className="mb-1 block text-slate-600">پایان</span>
                                    <PersianDatePicker
                                        value={form.data.end_date}
                                        disabled={form.data.is_current}
                                        onChange={(v) => form.setData('end_date', v)}
                                    />
                                </label>
                            </div>
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_current}
                                    onChange={(e) => form.setData('is_current', e.target.checked)}
                                />
                                هنوز ادامه دارد
                            </label>
                            <label className="block text-sm">
                                <span className="mb-1 block text-slate-600">لینک (اختیاری)</span>
                                <input
                                    dir="ltr"
                                    type="url"
                                    placeholder="https://"
                                    className="w-full rounded-lg border px-3 py-2"
                                    value={form.data.link}
                                    onChange={(e) => form.setData('link', e.target.value)}
                                />
                            </label>
                            <label className="block text-sm">
                                <span className="mb-1 block text-slate-600">توضیحات</span>
                                <textarea
                                    className="min-h-24 w-full rounded-lg border px-3 py-2"
                                    value={form.data.description}
                                    onChange={(e) => form.setData('description', e.target.value)}
                                />
                            </label>

                            {existingAttachments.length > 0 ? (
                                <div className="text-sm">
                                    <p className="mb-2 font-medium text-slate-700">پیوست‌های فعلی</p>
                                    <ul className="space-y-2">
                                        {existingAttachments.map((file) => {
                                            const marked = removeAttachmentIds.includes(file.id);

                                            return (
                                                <li
                                                    key={file.id}
                                                    className={`flex items-center justify-between gap-2 rounded-lg border px-3 py-2 ${
                                                        marked ? 'border-red-200 bg-red-50 opacity-60' : 'border-slate-200'
                                                    }`}
                                                >
                                                    <span className="truncate text-slate-700">
                                                        {file.name} ({formatFileSize(file.size)})
                                                    </span>
                                                    <button
                                                        type="button"
                                                        className="shrink-0 text-xs font-medium text-red-600"
                                                        onClick={() => toggleRemoveAttachment(file.id)}
                                                    >
                                                        {marked ? 'بازگردانی' : 'حذف'}
                                                    </button>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                </div>
                            ) : null}

                            {keptExistingCount < attachmentLimits.max_files ? (
                                <FileUploader
                                    value={newAttachments}
                                    onChange={setNewAttachments}
                                    multiple
                                    accept=".pdf,image/jpeg,image/png,image/webp"
                                    maxFiles={attachmentLimits.max_files - keptExistingCount + newAttachments.length}
                                    maxSizeBytes={attachmentLimits.max_kilobytes * 1024}
                                    labels={{
                                        dropzoneTitle: 'مدرک، گواهی یا PDF پیوست کنید',
                                        dropzoneHint: 'حداکثر ۱۰ مگابایت — PDF یا تصویر',
                                    }}
                                    hint={`حداکثر ${attachmentLimits.max_files.toLocaleString('fa-IR')} فایل برای هر مورد`}
                                />
                            ) : (
                                <p className="text-xs text-amber-700">سقف پیوست برای این مورد پر شده است.</p>
                            )}

                            <div className="flex gap-2 pt-2">
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                                >
                                    {editingId ? 'ذخیره تغییرات' : 'ثبت مورد'}
                                </button>
                                <button type="button" onClick={resetForm} className="rounded-lg border px-4 py-2 text-sm">
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            ) : null}
        </InstructorLayout>
    );
}
