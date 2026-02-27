import { UserGroup } from '../hooks/useFetchUserGroups';
import { EnrichCategory } from '../models';
import { CategoryPermissions } from '../models/CategoryPermission';
declare type Props = {
    category: EnrichCategory;
    applyPermissionsOnChildren: boolean;
    onChangePermissions: (userGroups: UserGroup[], type: keyof CategoryPermissions, values: number[]) => void;
    onChangeApplyPermissionsOnChildren: (value: boolean) => void;
};
declare const EditPermissionsForm: ({ category, applyPermissionsOnChildren, onChangePermissions, onChangeApplyPermissionsOnChildren, }: Props) => JSX.Element;
export { EditPermissionsForm };
