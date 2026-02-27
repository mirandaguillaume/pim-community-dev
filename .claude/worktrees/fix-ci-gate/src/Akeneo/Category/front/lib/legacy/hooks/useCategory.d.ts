import { FetchStatus } from '@akeneo-pim-community/shared';
import { Category } from '../models';
import type { EditCategoryForm } from '../models/Category';
declare type EditCategoryData = {
    category: Category;
    form: EditCategoryForm;
};
declare const useCategory: (categoryId: number) => [data: EditCategoryData | null, fetch: () => Promise<void>, status: FetchStatus, error: string | null];
export { useCategory };
export type { EditCategoryForm };
