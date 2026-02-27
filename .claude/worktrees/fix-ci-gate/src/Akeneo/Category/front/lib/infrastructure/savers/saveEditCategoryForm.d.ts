import { Router } from '@akeneo-pim-community/shared';
import { EnrichCategory } from '../../models';
interface EditCategoryResponseOK {
    success: true;
    category: EnrichCategory;
}
interface EditCategoryResponseKO {
    success: false;
    error: {
        code?: string;
        message: string;
    };
}
declare type EditCategoryResponse = EditCategoryResponseOK | EditCategoryResponseKO;
interface SaveOptions {
    applyPermissionsOnChildren: boolean;
    populateResponseCategory: (c: EnrichCategory) => EnrichCategory;
}
declare const saveEditCategoryForm: (router: Router, category: EnrichCategory, options: SaveOptions) => Promise<EditCategoryResponse>;
export { saveEditCategoryForm };
