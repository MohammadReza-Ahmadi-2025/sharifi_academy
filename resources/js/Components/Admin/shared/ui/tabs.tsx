"use client";

interface TabNavProps<T extends string> {
  tabs: { value: T; label: string }[];
  activeTab: T;
  onChange: (tab: T) => void;
}

export function TabNav<T extends string>({
  tabs,
  activeTab,
  onChange,
}: TabNavProps<T>) {
  return (
    <div className="border-b border-slate-200 dark:border-slate-800">
      <div className="flex gap-6 overflow-x-auto">
        {tabs.map((tab) => (
          <button
            key={tab.value}
            type="button"
            onClick={() => onChange(tab.value)}
            className={`relative shrink-0 pb-3 text-sm font-semibold transition-all ${
              activeTab === tab.value
                ? "text-slate-900 dark:text-slate-100"
                : "text-slate-400 hover:text-slate-650 dark:text-slate-500 dark:hover:text-slate-400"
            }`}
          >
            {tab.label}
            {activeTab === tab.value && (
              <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-slate-900 dark:bg-slate-100" />
            )}
          </button>
        ))}
      </div>
    </div>
  );
}
