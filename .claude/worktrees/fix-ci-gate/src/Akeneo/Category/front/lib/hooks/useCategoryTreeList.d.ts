import { CategoryTreeModel } from '../models';
declare const useCategoryTreeList: () => {
    trees: CategoryTreeModel[];
    loadingStatus: import("@akeneo-pim-community/shared").FetchStatus;
    loadTrees: () => Promise<void>;
    loadingError: string | null;
};
export { useCategoryTreeList };
