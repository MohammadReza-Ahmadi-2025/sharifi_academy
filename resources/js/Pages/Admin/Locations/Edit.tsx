import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { reorderByIndex } from '@/Components/shared/reorder-list';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { GripVertical } from 'lucide-react';
import { useState } from 'react';

type City = { id: number; title: string; sort: number };

export default function LocationEdit({ province, cities }: { province: { id: number; title: string }; cities: City[] }) {
    const form = useForm({ cities });
    const [dragIndex, setDragIndex] = useState<number | null>(null);
    const [dropIndex, setDropIndex] = useState<number | null>(null);
    const canDrag = form.data.cities.length > 1 && !form.processing;

    const reorderCities = (from: number, to: number) => {
        const next = reorderByIndex(form.data.cities, from, to).map((city, index) => ({
            ...city,
            sort: index,
        }));
        form.setData('cities', next);
    };

    return (
        <AdminLayout>
            <Head title={`شهرهای ${province.title}`} />
            <PageHeader title={`شهرهای ${province.title}`} backHref="/admin/locations" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => ({
                        cities: data.cities.map((city, index) => ({
                            id: city.id,
                            title: city.title,
                            sort: index,
                        })),
                    }));
                    form.put(`/admin/locations/${province.id}`);
                }}
            >
                {canDrag && (
                    <p className="text-sm text-slate-500">برای تغییر ترتیب شهرها، ردیف‌ها را با دستگیره بکشید.</p>
                )}
                <Card className="divide-y overflow-hidden">
                    {form.data.cities.map((city, index) => (
                        <div
                            key={city.id}
                            className={cn(
                                'grid gap-3 p-4 md:grid-cols-[auto_1fr]',
                                dragIndex === index && 'opacity-50',
                                dropIndex === index && dragIndex !== index && 'bg-slate-50 dark:bg-slate-900/40',
                            )}
                            draggable={canDrag}
                            onDragStart={() => canDrag && setDragIndex(index)}
                            onDragEnd={() => {
                                setDragIndex(null);
                                setDropIndex(null);
                            }}
                            onDragOver={(e) => {
                                if (!canDrag || dragIndex === null) {
                                    return;
                                }
                                e.preventDefault();
                                setDropIndex(index);
                            }}
                            onDrop={() => {
                                if (dragIndex === null || dragIndex === index) {
                                    setDragIndex(null);
                                    setDropIndex(null);

                                    return;
                                }
                                reorderCities(dragIndex, index);
                                setDragIndex(null);
                                setDropIndex(null);
                            }}
                        >
                            {canDrag ? (
                                <span className="cursor-grab self-center text-slate-400 active:cursor-grabbing" aria-hidden>
                                    <GripVertical className="size-5" />
                                </span>
                            ) : (
                                <span />
                            )}
                            <FormField label="نام شهر">
                                <Input
                                    value={city.title}
                                    onChange={(e) => {
                                        const next = [...form.data.cities];
                                        next[index] = { ...city, title: e.target.value };
                                        form.setData('cities', next);
                                    }}
                                />
                            </FormField>
                        </div>
                    ))}
                </Card>
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره شهرها</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
