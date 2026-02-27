import { Router } from '@akeneo-pim-community/shared';
declare type MovedCategory = {
    identifier: number;
    parentId: number;
    previousCategoryId: number | null;
};
declare const moveCategory: (router: Router, movedCategory: MovedCategory) => Promise<boolean>;
export { moveCategory };
