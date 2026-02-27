import { ChannelCode, LabelCollection, LocaleCode } from '@akeneo-pim-community/shared';
import { TreeNode } from './Tree';
import { CompositeKeyWithoutLocale } from './CompositeKey';
import { CategoryAttributeType } from './Attribute';
import { CategoryPermissions } from './CategoryPermission';
export declare type Category = {
    id: number;
    code: string;
    labels: LabelCollection;
    root: Category | null;
};
export declare type EnrichCategory = {
    id: number;
    isRoot: boolean;
    template_uuid: string | null;
    root: EnrichCategory | null;
    properties: CategoryProperties;
    attributes: CategoryAttributes;
    permissions: CategoryPermissions;
};
export declare type CategoryProperties = {
    code: string;
    labels: LabelCollection;
};
export interface CategoryAttributes {
    [key: string]: CategoryAttributeValueWrapper;
}
export interface CategoryAttributeValueWrapper {
    data: CategoryAttributeValueData;
    channel: ChannelCode | null;
    locale: LocaleCode | null;
    attribute_code: CompositeKeyWithoutLocale;
}
declare type CategoryTextAttributeValueData = string;
export interface CategoryImageAttributeValueDataFileInfo {
    size?: number;
    file_path: string;
    mime_type?: string;
    extension?: string;
    original_filename: string;
}
export declare type CategoryImageAttributeValueData = CategoryImageAttributeValueDataFileInfo | null;
export declare type CategoryAttributeValueData = CategoryTextAttributeValueData | CategoryImageAttributeValueData;
export declare const isCategoryImageAttributeValueData: (data: CategoryAttributeValueData) => data is CategoryImageAttributeValueData;
export declare type BackendCategoryTree = {
    attr: {
        id: string;
        'data-code': string;
    };
    data: string;
    state: 'leaf' | 'closed' | 'closed jstree-root';
    children?: BackendCategoryTree[];
};
export declare type CategoryTreeModel = {
    id: number;
    code: string;
    label: string;
    isRoot: boolean;
    isLeaf: boolean;
    children?: CategoryTreeModel[];
    productsNumber?: number;
    templateLabel?: string;
    templateUuid?: string;
    templateCode?: string;
};
export declare type FormField = {
    value: string;
    fullName: string;
    label: string;
};
export declare type HiddenFormField = {
    value: string;
    fullName: string;
};
export declare type FormChoiceField = {
    value: string[];
    fullName: string;
    choices: {
        value: string;
        label: string;
    }[];
};
export declare type EditCategoryForm = {
    label: {
        [locale: string]: FormField;
    };
    _token: HiddenFormField;
    permissions?: {
        view: FormChoiceField;
        edit: FormChoiceField;
        own: FormChoiceField;
        apply_on_children: HiddenFormField;
    };
    errors: string[];
};
export declare const RICH_TEXT_DEFAULT_VALUE = "<p></p>\n";
export declare const attributeDefaultValues: {
    [key in CategoryAttributeType]: CategoryAttributeValueData;
};
declare const convertToCategoryTree: (tree: BackendCategoryTree) => CategoryTreeModel;
declare const buildTreeNodeFromCategoryTree: (categoryTree: CategoryTreeModel, parent?: number | null) => TreeNode<CategoryTreeModel>;
export { convertToCategoryTree, buildTreeNodeFromCategoryTree };
