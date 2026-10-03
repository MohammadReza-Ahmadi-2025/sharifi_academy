import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type City = { id: number; title: string; sort: number };

export default function LocationEdit({ province, cities }: { province: { id: number; title: string }; cities: City[] }) {
    const form = useForm({ cities });
    return (
        <AdminLayout>
            <Head title={`شهرهای ${province.title}`} />
            <PageHeader title={`شهرهای ${province.title}`} backHref="/admin/locations" />
            <form onSubmit={(e) => { e.preventDefault(); form.put(`/admin/locations/${province.id}`); }} className="mt-6 space-y-4">
                <Card className="divide-y overflow-hidden">
                    {form.data.cities.map((city, index) => (
                        <div key={city.id} className="grid gap-3 p-4 md:grid-cols-2">
                            <FormField label="نام شهر"><Input value={city.title} onChange={(e) => { const next = [...form.data.cities]; next[index] = { ...city, title: e.target.value }; form.setData('cities', next); }} /></FormField>
                            <FormField label="ترتیب"><Input type="number" value={city.sort} onChange={(e) => { const next = [...form.data.cities]; next[index] = { ...city, sort: Number(e.target.value) }; form.setData('cities', next); }} /></FormField>
                        </div>
                    ))}
                </Card>
                <AdminButton type="submit" disabled={form.processing}>ذخیره شهرها</AdminButton>
            </form>
        </AdminLayout>
    );
}
