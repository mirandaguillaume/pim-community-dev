import { Router } from '@akeneo-pim-community/shared';
import { EditCategoryForm } from '../../models';
import { Category } from '../../models';
declare type EditCategoryResponse = {
    success: boolean;
    form: EditCategoryForm;
    category: Category;
};
declare const saveEditCategoryForm: (router: Router, id: number, formData: EditCategoryForm) => Promise<EditCategoryResponse>;
export { saveEditCategoryForm };
