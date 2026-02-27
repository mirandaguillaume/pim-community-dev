import { ApiError } from '../tools/apiFetch';
declare type Data = {
    labels: {
        [localeCode: string]: string;
    };
};
declare type Error = ApiError<{
    labels: {
        [localeCode: string]: string[];
    };
}>;
export declare const useUpdateTemplateProperties: (templateUuid: string) => import("react-query").UseMutationResult<void, Error, Data, unknown>;
export {};
