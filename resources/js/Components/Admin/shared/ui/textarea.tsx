import { ckEditorHeightFromRows, loadCkEditor4, type RichTextareaNativeProps } from '@/lib/ckeditor';
import { cn } from '@/lib/utils';
import * as React from 'react';

export type TextareaProps = RichTextareaNativeProps & {
    /** غیرفعال کردن CKEditor و استفاده از textarea ساده */
    plain?: boolean;
};

const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>(
    ({ className, plain, value, defaultValue, onChange, onBlur, disabled, rows, id, name, ...props }, ref) => {
        const generatedId = React.useId();
        const fieldId = id ?? generatedId;
        const textareaRef = React.useRef<HTMLTextAreaElement | null>(null);
        const editorRef = React.useRef<{ getData: () => string; setData: (d: string) => void; destroy: () => void } | null>(
            null,
        );
        const changeHandlerRef = React.useRef<(() => void) | null>(null);
        const [editorReady, setEditorReady] = React.useState(false);

        React.useImperativeHandle(ref, () => textareaRef.current as HTMLTextAreaElement);

        React.useEffect(() => {
            if (plain || disabled) {
                return;
            }

            const element = textareaRef.current;
            if (!element) {
                return;
            }

            let cancelled = false;

            loadCkEditor4()
                .then((CKEDITOR) => {
                    if (cancelled || !textareaRef.current) {
                        return;
                    }

                    const instance = CKEDITOR.replace(textareaRef.current, {
                        language: 'fa',
                        contentsLangDirection: 'rtl',
                        height: ckEditorHeightFromRows(rows),
                        removePlugins: 'elementspath',
                        resize_enabled: true,
                        versionCheck: false,
                    });

                    const initial = (value ?? defaultValue ?? '') as string;
                    if (initial) {
                        instance.setData(initial);
                    }

                    const onEditorChange = () => {
                        const data = instance.getData();
                        onChange?.({
                            target: { value: data, name: name ?? '' },
                            currentTarget: { value: data, name: name ?? '' },
                        } as React.ChangeEvent<HTMLTextAreaElement>);
                    };

                    instance.on('change', onEditorChange);
                    instance.on('blur', () => {
                        onBlur?.({
                            target: { value: instance.getData(), name: name ?? '' },
                        } as React.FocusEvent<HTMLTextAreaElement>);
                    });

                    changeHandlerRef.current = onEditorChange;
                    editorRef.current = instance;
                    setEditorReady(true);
                })
                .catch(() => {
                    setEditorReady(false);
                });

            return () => {
                cancelled = true;
                const instance = editorRef.current;
                if (instance) {
                    instance.destroy();
                    editorRef.current = null;
                }
                setEditorReady(false);
            };
            // eslint-disable-next-line react-hooks/exhaustive-deps -- mount once per plain/disabled/rows
        }, [plain, disabled, rows]);

        React.useEffect(() => {
            const instance = editorRef.current;
            if (!instance || plain) {
                return;
            }

            const next = (value ?? '') as string;
            if (instance.getData() !== next) {
                instance.setData(next);
            }
        }, [value, plain, editorReady]);

        if (plain) {
            return (
                <textarea
                    className={cn(
                        'flex min-h-24 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-slate-900',
                        'dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-slate-200',
                        className,
                    )}
                    ref={textareaRef}
                    value={value}
                    defaultValue={defaultValue}
                    onChange={onChange}
                    onBlur={onBlur}
                    disabled={disabled}
                    rows={rows}
                id={fieldId}
                name={name}
                {...props}
            />
        );
    }

        return (
            <textarea
                className={cn('sr-only', className)}
                ref={textareaRef}
                value={value}
                defaultValue={defaultValue}
                onChange={onChange}
                onBlur={onBlur}
                disabled={disabled}
                rows={rows}
                id={fieldId}
                name={name}
                aria-hidden={editorReady}
                {...props}
            />
        );
    },
);

Textarea.displayName = 'Textarea';

export { Textarea };
