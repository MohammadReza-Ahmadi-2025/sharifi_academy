import { RoleCard, type AdminRoleListItem } from '@/Components/Admin/Roles/RoleCard';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Props = {
    roles: AdminRoleListItem[];
    filters: { search: string };
    abilities: { create: boolean };
};

export default function RolesIndex({ roles, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        router.get('/admin/roles', { search: search || undefined }, { preserveState: true, replace: true });
    };

    const handleDelete = (role: AdminRoleListItem) => {
        if (!window.confirm(`نقش «${role.label}» حذف شود؟`)) {
            return;
        }

        router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <Head title="نقش‌ها و دسترسی‌ها" />

            <PageHeader
                title="نقش‌ها و دسترسی‌ها"
                description="تعریف نقش‌ها و تخصیص مجوزها به هر نقش"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/roles/create">
                            <Plus className="ml-1 h-4 w-4" />
                            نقش جدید
                        </AdminButton>
                    ) : null
                }
            />

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={submitSearch}
                onClear={() => {
                    setSearch('');
                    router.get('/admin/roles', {}, { replace: true });
                }}
                searchPlaceholder="جستجو بر اساس نام نقش…"
            />

            <div className="mt-6">
                {roles.length === 0 ? (
                    <Card className="p-8 text-center">
                        <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">نقشی پیدا نشد</h3>
                        <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">فیلتر را تغییر دهید یا نقش جدید بسازید.</p>
                    </Card>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                        {roles.map((role) => (
                            <RoleCard key={role.id} role={role} onDelete={handleDelete} />
                        ))}
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
