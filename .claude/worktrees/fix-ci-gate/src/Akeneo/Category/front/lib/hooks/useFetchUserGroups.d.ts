import { ResponseStatus } from '../models/ResponseStatus';
declare type ResultError = Error | null;
declare type Result = {
    status: ResponseStatus;
    data: UserGroup[] | undefined;
    error: ResultError;
};
export declare type UserGroup = {
    id: string;
    label: string;
    isDefault: boolean;
};
export declare const useFetchUserGroups: () => Result;
export {};
