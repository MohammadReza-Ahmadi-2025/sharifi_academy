import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Link } from '@inertiajs/react';
import { KeyRound, ShieldCheck, SquarePen, Trash2, Users2 } from 'lucide-react';

export type AdminRoleListItem = {
    id: number;
    name: string;
    label: string;
    usersCount: number;
    permissionsCount: number;
    isSystem: boolean;
    canUpdate: boolean;
    canDelete: boolean;
};

type RoleCardProps = {
    role: AdminRoleListItem;
    onDelete: (role: AdminRoleListItem) => void;
};

export function RoleCard({ role, onDelete }: RoleCardProps) {
    return (
        <Card>
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <h3 className="truncate text-lg font-semibold text-slate-900 dark:text-slate-100">{role.label}</h3>
                        <Badge variant={role.isSystem ? 'info' : 'secondary'}>
                            {role.isSystem ? 'سیستمی' : 'سفارشی'}
                        </Badge>
                    </div>
                    <p className="mt-1 font-mono text-[11px] text-slate-400 dark:text-slate-500">{role.name}</p>
                </div>
            </div>

            <div className="mt-4 grid grid-cols-2 gap-3">
                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/60">
                    <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <Users2 className="h-3.5 w-3.5" />
                        کاربران
                    </div>
                    <div className="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {role.usersCount.toLocaleString('fa-IR')}
                    </div>
                </div>

                <div className="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/60">
                    <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <KeyRound className="h-3.5 w-3.5" />
                        مجوزها
                    </div>
                    <div className="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {role.permissionsCount.toLocaleString('fa-IR')}
                    </div>
                </div>
            </div>

            <div className="mt-5 flex items-center justify-between gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                <div className="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                    <ShieldCheck className="h-4 w-4" />
                    <span>مدیریت سطح دسترسی</span>
                </div>

                <div className="flex items-center gap-1">
                    {role.canDelete && (
                        <AdminButton
                            type="button"
                            variant="outline"
                            className="h-8 px-2 text-xs text-rose-600 hover:text-rose-700 dark:text-rose-400"
                            onClick={() => onDelete(role)}
                        >
                            <Trash2 className="ml-1 h-3.5 w-3.5" />
                            حذف
                        </AdminButton>
                    )}

                    {role.canUpdate && (
                        <Link
                            href={`/admin/roles/${role.id}/edit`}
                            className="inline-flex h-8 items-center rounded-md border border-slate-200 px-2 text-xs font-medium text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-900"
                        >
                            <SquarePen className="ml-1 h-3.5 w-3.5" />
                            ویرایش
                        </Link>
                    )}
                </div>
            </div>
        </Card>
    );
}
