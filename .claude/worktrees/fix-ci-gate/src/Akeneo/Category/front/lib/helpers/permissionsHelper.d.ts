import { CategoryPermission, CategoryPermissions } from '../models/CategoryPermission';
import { UserGroup } from '../hooks/useFetchUserGroups';
interface PermissionChanges {
    type: keyof CategoryPermissions;
    values: number[];
}
export declare function ensureSubset(permissions1: CategoryPermission[], permission2: CategoryPermission[]): CategoryPermission[];
export declare function ensureSuperset(permissions1: CategoryPermission[], permissions2: CategoryPermission[]): CategoryPermission[];
export declare function alterPermissionsConsistently(userGroups: UserGroup[], permissions: CategoryPermissions, changes: PermissionChanges): CategoryPermissions;
export {};
