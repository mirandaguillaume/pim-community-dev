import { FetchStatus } from '@akeneo-pim-community/shared';
import { CategoryTreeModel } from '../models';
declare const useCategoryTreeDeprecated: (treeId: number, lastSelectedCategoryId: string) => {
    tree: CategoryTreeModel | null;
    loadTree: () => Promise<void>;
    loadingStatus: FetchStatus;
    error: string | null;
};
export { useCategoryTreeDeprecated };
