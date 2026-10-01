import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Checkbox } from '@/Components/Admin/shared/ui/checkbox';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { useMemo, useState } from 'react';

export type PermissionGroup = {
    id: string;
    label: string;
    permissions: { slug: string; title: string }[];
};

type RolePermissionsPickerProps = {
    permissionGroups: PermissionGroup[];
    selected: string[];
    onChange: (permissions: string[]) => void;
    error?: string;
    disabled?: boolean;
};

export function RolePermissionsPicker({
    permissionGroups,
    selected,
    onChange,
    error,
    disabled = false,
}: RolePermissionsPickerProps) {
    const [searchQuery, setSearchQuery] = useState('');

    const filteredGroups = useMemo(() => {
        const query = searchQuery.trim().toLowerCase();
        if (!query) {
            return permissionGroups;
        }

        return permissionGroups
            .map((group) => ({
                ...group,
                permissions: group.permissions.filter(
                    (permission) =>
                        permission.title.toLowerCase().includes(query) ||
                        permission.slug.toLowerCase().includes(query) ||
                        group.label.toLowerCase().includes(query),
                ),
            }))
            .filter((group) => group.permissions.length > 0);
    }, [permissionGroups, searchQuery]);

    const togglePermission = (slug: string) => {
        if (disabled) {
            return;
        }

        if (selected.includes(slug)) {
            onChange(selected.filter((item) => item !== slug));
        } else {
            onChange([...selected, slug]);
        }
    };

    const groupSlugs = (group: PermissionGroup) => group.permissions.map((p) => p.slug);

    const isGroupFullySelected = (group: PermissionGroup) => {
        const slugs = groupSlugs(group);

        return slugs.length > 0 && slugs.every((slug) => selected.includes(slug));
    };

    const isGroupPartiallySelected = (group: PermissionGroup) => {
        const slugs = groupSlugs(group);
        const count = slugs.filter((slug) => selected.includes(slug)).length;

        return count > 0 && count < slugs.length;
    };

    const toggleGroup = (group: PermissionGroup) => {
        if (disabled) {
            return;
        }

        const slugs = groupSlugs(group);
        if (isGroupFullySelected(group)) {
            onChange(selected.filter((slug) => !slugs.includes(slug)));
        } else {
            onChange([...new Set([...selected, ...slugs])]);
        }
    };

    return (
        <Card className="space-y-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">دسترسی‌ها</h2>
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">مجوزهای مورد نیاز این نقش را انتخاب کنید</p>
                </div>
                <Badge variant="secondary">{selected.length.toLocaleString('fa-IR')} دسترسی انتخاب شده</Badge>
            </div>

            <AdminInput
                value={searchQuery}
                onChange={(event) => setSearchQuery(event.target.value)}
                placeholder="جستجو در دسترسی‌ها..."
                disabled={disabled}
            />

            <div className="space-y-4">
                {filteredGroups.length > 0 ? (
                    filteredGroups.map((group) => {
                        const fullySelected = isGroupFullySelected(group);
                        const partiallySelected = isGroupPartiallySelected(group);

                        return (
                            <div key={group.id} className="rounded-xl border border-slate-200 dark:border-slate-800">
                                <div className="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/60">
                                    <Checkbox
                                        checked={fullySelected}
                                        disabled={disabled}
                                        ref={(input) => {
                                            if (input) {
                                                input.indeterminate = partiallySelected;
                                            }
                                        }}
                                        onChange={() => toggleGroup(group)}
                                        label={group.label}
                                    />
                                </div>

                                <div className="grid gap-2 p-4 sm:grid-cols-2">
                                    {group.permissions.map((permission) => (
                                        <Checkbox
                                            key={permission.slug}
                                            checked={selected.includes(permission.slug)}
                                            disabled={disabled}
                                            onChange={() => togglePermission(permission.slug)}
                                            label={permission.title}
                                            description={permission.slug}
                                        />
                                    ))}
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <p className="text-sm text-slate-500 dark:text-slate-400">دسترسی‌ای با این جستجو پیدا نشد.</p>
                )}
            </div>

            {error ? <p className="text-xs text-rose-500">{error}</p> : null}
        </Card>
    );
}
