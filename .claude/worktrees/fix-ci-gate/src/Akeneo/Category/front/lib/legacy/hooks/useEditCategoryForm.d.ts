import { EditCategoryForm } from './useCategory';
import { Category } from '../models';
declare const useEditCategoryForm: (categoryId: number) => {
    categoryLoadingStatus: import("@akeneo-pim-community/shared").FetchStatus;
    category: Category | null;
    formData: EditCategoryForm | null;
    onChangeCategoryLabel: (locale: string, label: string) => void;
    onChangePermissions: (type: string, values: any) => void;
    onChangeApplyPermissionsOnChildren: (value: any) => void;
    thereAreUnsavedChanges: boolean;
    saveCategory: () => Promise<void>;
    historyVersion: number;
};
export { useEditCategoryForm };
