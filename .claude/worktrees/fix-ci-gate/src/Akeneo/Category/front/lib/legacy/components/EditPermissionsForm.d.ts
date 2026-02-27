import { EditCategoryForm } from '../models';
declare type Props = {
    formData: EditCategoryForm | null;
    onChangePermissions: (type: string, values: string[]) => void;
    onChangeApplyPermissionsOnChildren: (value: boolean) => void;
};
declare const EditPermissionsForm: ({ formData, onChangePermissions, onChangeApplyPermissionsOnChildren }: Props) => JSX.Element;
export { EditPermissionsForm };
