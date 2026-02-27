import { Category, EditCategoryForm } from '../models';
declare type Props = {
    category: Category;
    formData: EditCategoryForm | null;
    onChangeLabel: (locale: string, label: string) => void;
};
declare const EditPropertiesForm: ({ category, formData, onChangeLabel }: Props) => JSX.Element;
export { EditPropertiesForm };
