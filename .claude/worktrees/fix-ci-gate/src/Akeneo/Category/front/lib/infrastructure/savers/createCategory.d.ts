import { Router } from '@akeneo-pim-community/shared';
declare type ValidationErrors = {
    [fieldCode: string]: string;
};
declare const createCategory: (router: Router, code: string, parent?: string | undefined, locale?: string | undefined, label?: string | undefined) => Promise<ValidationErrors>;
export { createCategory };
export type { ValidationErrors };
