import { Router } from '@akeneo-pim-community/shared';
declare type Response = {
    ok: boolean;
    errorMessage: string;
};
declare const deleteCategory: (router: Router, id: number) => Promise<Response>;
export { deleteCategory };
