import { LabelCollection } from '@akeneo-pim-community/shared';
import { TreeNode } from './Tree';
export declare type Category = {
    id: number;
    code: string;
    labels: LabelCollection;
    root: Category | null;
};
export declare type EditableCategoryProperties = {
    labels: LabelCollection;
};
export declare type CategoryPermissions = {
    view: number[];
    edit: number[];
    own: number[];
    apply_on_children: '0' | '1';
};
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
declare const convertToCategoryTree: (tree: BackendCategoryTree) => CategoryTreeModel;
declare const buildTreeNodeFromCategoryTree: (categoryTree: CategoryTreeModel, parent?: number | null) => TreeNode<CategoryTreeModel>;
export { convertToCategoryTree, buildTreeNodeFromCategoryTree };
