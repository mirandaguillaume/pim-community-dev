declare type UnsavedChangesContextValue = {
    hasUnsavedChanges: boolean;
    setHasUnsavedChanges: (newValue: boolean) => void;
};
declare const UnsavedChangesContext: import("react").Context<UnsavedChangesContextValue>;
export { UnsavedChangesContext };
export type { UnsavedChangesContextValue };
