import { BadRequestError } from '../tools/apiFetch';
declare type Form = {
    templateId: string;
    code: string;
    locale: string;
    label: string | null;
    type: string;
    isLocalizable: boolean;
    isScopable: boolean;
};
declare type CreateAttributeErrors = {
    [property: string]: string[];
};
export declare const useCreateAttribute: () => import("react-query").UseMutationResult<void, BadRequestError<CreateAttributeErrors>, Form, unknown>;
export {};
