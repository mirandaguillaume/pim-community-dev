export declare type CategoryPermission = {
    id: number;
    label: string;
};
export declare type CategoryPermissions = {
    view: CategoryPermission[];
    edit: CategoryPermission[];
    own: CategoryPermission[];
};
