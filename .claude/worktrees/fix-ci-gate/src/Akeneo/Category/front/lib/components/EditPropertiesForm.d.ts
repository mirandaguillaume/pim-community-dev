import { EnrichCategory } from '../models';
declare type Props = {
    category: EnrichCategory;
    onChangeLabel: (locale: string, label: string) => void;
};
declare const EditPropertiesForm: ({ category, onChangeLabel }: Props) => JSX.Element;
export { EditPropertiesForm };
