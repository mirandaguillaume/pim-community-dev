import { ApiError } from '../tools/apiFetch';
declare type Data = {
    isRichTextArea?: boolean;
    labels?: {
        [locale: string]: string;
    };
};
declare type Error = {
    labels: {
        [localeCode: string]: string[];
    };
};
export declare const useUpdateTemplateAttribute: (templateUuid: string, attributeUuid: string) => import("react-query").UseMutationResult<void, ApiError<Error>, Data, unknown>;
export {};
