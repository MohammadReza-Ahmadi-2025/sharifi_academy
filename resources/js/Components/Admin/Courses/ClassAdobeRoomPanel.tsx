import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Select } from '@/Components/Admin/shared/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableEmptyState,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import { router, useForm } from '@inertiajs/react';
import { Plus, RefreshCw, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent } from 'react';

type AdobeRoomRow = {
    id: number;
    sco_id: string;
    url_path: string;
    meeting_url?: string | null;
    status_label: string;
};

type Props = {
    routePrefix: string;
    courseId: number;
    classId: number;
    rooms: AdobeRoomRow[];
    adobeAccessOptions: { value: string; label: string }[];
    adobeStatusOptions: { value: string; label: string }[];
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
};

export function ClassAdobeRoomPanel({
    routePrefix,
    courseId,
    classId,
    rooms,
    adobeAccessOptions,
    adobeStatusOptions,
    canCreate,
    canUpdate,
    canDelete,
}: Props) {
    const confirmToast = useConfirmToast();
    if (!canCreate && rooms.length === 0) {
        return null;
    }

    const baseUrl = `${routePrefix}/courses/${courseId}/classes/${classId}/adobe-rooms`;
    const accessOptions = mapToSelectOptions(adobeAccessOptions);
    const statusOptions = mapToSelectOptions(adobeStatusOptions);

    const form = useForm({
        sco_id: '',
        url_path: '',
        meeting_url: '',
        access_type: adobeAccessOptions[0]?.value ?? 'protected',
        passcode: '',
        status: adobeStatusOptions[0]?.value ?? 'active',
    });

    const submitRoom = (event: FormEvent) => {
        event.preventDefault();
        form.post(baseUrl, {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const removeRoom = (room: AdobeRoomRow) => {
        confirmToast(`اتاق «${room.sco_id}» حذف شود؟`, () => {
        router.delete(`${baseUrl}/${room.id}`, { preserveScroll: true });
    });
    };

    return (
        <Card className="mt-8 space-y-6 p-6">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اتاق ادوبی (سطح کلاس)</h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    برای کلاس‌های Adobe Connect، با ذخیره کلاس اتاق معمولاً خودکار ساخته می‌شود. در صورت نیاز دستی SCO ID وارد کنید.
                </p>
            </div>

            {canCreate ? (
                <form onSubmit={submitRoom} className="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="SCO ID" htmlFor="adobe-sco" required error={form.errors.sco_id}>
                            <AdminInput
                                id="adobe-sco"
                                dir="ltr"
                                className="font-mono text-left"
                                disabled={form.processing}
                                value={form.data.sco_id}
                                onChange={(e) => form.setData('sco_id', e.target.value)}
                            />
                        </FormField>
                        <FormField label="URL path" htmlFor="adobe-path" hint="اختیاری؛ با sco-id از API دریافت می‌شود" error={form.errors.url_path}>
                            <AdminInput
                                id="adobe-path"
                                dir="ltr"
                                className="font-mono text-left"
                                disabled={form.processing}
                                value={form.data.url_path}
                                onChange={(e) => form.setData('url_path', e.target.value)}
                            />
                        </FormField>
                        <FormField label="Meeting URL" htmlFor="adobe-meeting" hint="اختیاری؛ از API ساخته می‌شود" className="md:col-span-2" error={form.errors.meeting_url}>
                            <AdminInput
                                id="adobe-meeting"
                                dir="ltr"
                                className="text-left"
                                disabled={form.processing}
                                value={form.data.meeting_url}
                                onChange={(e) => form.setData('meeting_url', e.target.value)}
                            />
                        </FormField>
                        <FormField label="نوع دسترسی" required error={form.errors.access_type}>
                            <Select
                                isDisabled={form.processing}
                                options={accessOptions}
                                value={findSelectOption(accessOptions, form.data.access_type)}
                                onChange={(option) => form.setData('access_type', option?.value ?? '')}
                            />
                        </FormField>
                        <FormField label="وضعیت اتاق" required error={form.errors.status}>
                            <Select
                                isDisabled={form.processing}
                                options={statusOptions}
                                value={findSelectOption(statusOptions, form.data.status)}
                                onChange={(option) => form.setData('status', option?.value ?? '')}
                            />
                        </FormField>
                        <FormField label="رمز عبور (اختیاری)" htmlFor="adobe-pass" className="md:col-span-2" error={form.errors.passcode}>
                            <AdminInput
                                id="adobe-pass"
                                disabled={form.processing}
                                value={form.data.passcode}
                                onChange={(e) => form.setData('passcode', e.target.value)}
                            />
                        </FormField>
                    </div>
                    <AdminButton type="submit" size="sm" disabled={form.processing}>
                        <Plus className="ml-1 h-4 w-4" />
                        افزودن اتاق
                    </AdminButton>
                </form>
            ) : null}

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>SCO ID</TableHead>
                        <TableHead>مسیر</TableHead>
                        <TableHead>وضعیت</TableHead>
                        {canUpdate || canDelete ? <TableHead className="w-32 text-left">عملیات</TableHead> : null}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {rooms.length === 0 ? (
                        <TableEmptyState colSpan={canUpdate || canDelete ? 4 : 3} title="اتاقی ثبت نشده" description="فرم بالا را پر کنید." />
                    ) : (
                        rooms.map((room) => (
                            <TableRow key={room.id}>
                                <TableCell dir="ltr" className="font-mono text-left text-sm">{room.sco_id}</TableCell>
                                <TableCell dir="ltr" className="font-mono text-left text-sm">{room.url_path}</TableCell>
                                <TableCell>
                                    <Badge variant="info">{room.status_label}</Badge>
                                </TableCell>
                                {canUpdate || canDelete ? (
                                    <TableCell className="flex gap-1">
                                        {canUpdate ? (
                                            <AdminButton
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                aria-label="سینک اتاق از Adobe"
                                                onClick={() => router.post(`${baseUrl}/${room.id}/sync`, {}, { preserveScroll: true })}
                                            >
                                                <RefreshCw className="h-4 w-4 text-indigo-600" />
                                            </AdminButton>
                                        ) : null}
                                        {canDelete ? (
                                        <AdminButton
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            aria-label="حذف اتاق"
                                            onClick={() => removeRoom(room)}
                                        >
                                            <Trash2 className="h-4 w-4 text-rose-600" />
                                        </AdminButton>
                                        ) : null}
                                    </TableCell>
                                ) : null}
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </Card>
    );
}
