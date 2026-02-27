import { EditCategoryForm } from '../models';
declare const computeNewViewPermissions: (originalFormData: EditCategoryForm, values: string[]) => EditCategoryForm;
declare const computeNewEditPermissions: (originalFormData: EditCategoryForm, values: string[]) => EditCategoryForm;
declare const computeNewOwnPermissions: (originalFormData: EditCategoryForm, values: string[]) => EditCategoryForm;
export { computeNewViewPermissions, computeNewEditPermissions, computeNewOwnPermissions };
