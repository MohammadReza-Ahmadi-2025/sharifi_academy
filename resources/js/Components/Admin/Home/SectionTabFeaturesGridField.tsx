import type { HomeSectionTabForm } from '@/Components/Admin/Home/HomeSectionFormFields';
import { FEATURES_GRID_COLUMNS } from '@/Components/Admin/Home/home-section-templates';
import { GalleryImagesField } from '@/Components/Admin/shared/GalleryImagesField';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { createExistingFileUploadItems, type FileUploadItem } from '@/Components/shared/file-upload';

export type FeatureGridRow = {
    label: string;
    text: string;
    image_url?: string | null;
    image?: FileUploadItem[];
};

export function defaultFeatureGridRows(): FeatureGridRow[] {
    return Array.from({ length: FEATURES_GRID_COLUMNS }, () => ({
        label: '',
        text: '',
        image_url: '',
        image: [],
    }));
}

export function featureGridRowsFromSettings(
    rows: { label?: string; text?: string; image_url?: string | null }[] | undefined,
): FeatureGridRow[] {
    const padded = [...(rows ?? [])];
    while (padded.length < FEATURES_GRID_COLUMNS) {
        padded.push({ label: '', text: '', image_url: '' });
    }

    return padded.slice(0, FEATURES_GRID_COLUMNS).map((row, index) => ({
        label: row.label ?? '',
        text: row.text ?? '',
        image_url: row.image_url ?? '',
        image: row.image_url
            ? createExistingFileUploadItems([
                  {
                      id: 900_000 + index,
                      url: row.image_url,
                      name: 'image',
                  },
              ])
            : [],
    }));
}

type Props = {
    tab: HomeSectionTabForm;
    disabled?: boolean;
    onChange: (settings: HomeSectionTabForm['settings']) => void;
};

export function SectionTabFeaturesGridField({ tab, disabled, onChange }: Props) {
    const features = featureGridRowsFromSettings(tab.settings.features);

    const updateRows = (next: FeatureGridRow[]) => {
        onChange({ ...tab.settings, features: next });
    };

    const updateRow = (index: number, patch: Partial<FeatureGridRow>) => {
        const next = [...features];
        next[index] = { ...next[index], ...patch };
        updateRows(next);
    };

    return (
        <div className="md:col-span-2 space-y-4">
            <p className="text-sm font-medium text-slate-700 dark:text-slate-200">
                گرید سه‌تایی این تب (حداکثر {FEATURES_GRID_COLUMNS} ستون)
            </p>
            {features.map((feature, featureIndex) => (
                <div key={featureIndex} className="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <p className="mb-2 text-xs font-medium text-slate-500">ستون {featureIndex + 1}</p>
                    <div className="grid gap-3">
                        <GalleryImagesField
                            title="تصویر ستون"
                            items={feature.image ?? []}
                            onItemsChange={(items) => updateRow(featureIndex, { image: items })}
                            multiple={false}
                            maxFiles={1}
                            disabled={disabled}
                        />
                        <FormField label="عنوان">
                            <AdminInput
                                value={feature.label}
                                disabled={disabled}
                                onChange={(e) => updateRow(featureIndex, { label: e.target.value })}
                            />
                        </FormField>
                        <FormField label="توضیح">
                            <AdminInput
                                value={feature.text}
                                disabled={disabled}
                                onChange={(e) => updateRow(featureIndex, { text: e.target.value })}
                            />
                        </FormField>
                    </div>
                </div>
            ))}
        </div>
    );
}
