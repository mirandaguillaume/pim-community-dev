import { BadRequestError } from '../tools/apiFetch';
declare type Form = {
    categoryTreeId: number;
    code: string;
    locale: string;
    label: string | null;
};
declare type MutationResult = {
    template_uuid: string;
};
export declare type CreateTemplateError = {
    templateCode: string[] | null;
    labels: {
        [locale: string]: string[];
    } | null;
};
export declare const useCreateTemplate: () => import("react-query").UseMutationResult<MutationResult, BadRequestError<CreateTemplateError>, Form, unknown>;
export {};
