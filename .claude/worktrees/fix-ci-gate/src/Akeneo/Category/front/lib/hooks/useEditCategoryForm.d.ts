import { Attribute, CategoryAttributeValueData, EnrichCategory, Template } from '../models';
import { CategoryPermissions } from '../models/CategoryPermission';
import { UserGroup } from './useFetchUserGroups';
declare const useEditCategoryForm: (categoryId: number) => {
    categoryFetchingStatus: import("@akeneo-pim-community/shared").FetchStatus;
    category: EnrichCategory | null;
    template: Template | undefined;
    applyPermissionsOnChildren: boolean;
    onChangeCategoryLabel: (localeCode: string, label: string) => void;
    onChangePermissions: (userGroups: UserGroup[], type: keyof CategoryPermissions, values: number[]) => void;
    onChangeAttribute: (attribute: Attribute, channelCode: string | null, localeCode: string | null, attributeValue: CategoryAttributeValueData) => void;
    onChangeApplyPermissionsOnChildren: import("react").Dispatch<import("react").SetStateAction<boolean>>;
    isModified: boolean;
    saveCategory: () => Promise<void>;
    historyVersion: number;
};
export { useEditCategoryForm };
