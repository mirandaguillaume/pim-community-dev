import { FetchStatus } from '@akeneo-pim-community/shared';
import type { EditCategoryForm } from '../models';
import { EnrichCategory } from '../models';
declare type CategoryResponse = {
    load: () => Promise<void>;
    status: FetchStatus;
    category: EnrichCategory | null;
    error: string | null;
};
declare const useCategory: (categoryId: number) => CategoryResponse;
export { useCategory };
export type { EditCategoryForm, CategoryResponse };
