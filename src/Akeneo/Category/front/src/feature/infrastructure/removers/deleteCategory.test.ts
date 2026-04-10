import {deleteCategory} from './deleteCategory';
import {Router} from '@akeneo-pim-community/shared';

const mockRouter: Router = {
    generate: jest.fn((route: string) => route),
    redirect: jest.fn(),
};

describe('deleteCategory', () => {
    beforeEach(() => jest.restoreAllMocks());

    it('returns ok=true and empty errorMessage on success', async () => {
        jest.spyOn(global, 'fetch').mockResolvedValueOnce(
            new Response('{}', {status: 200})
        );
        const result = await deleteCategory(mockRouter, 42);
        expect(result).toStrictEqual({ok: true, errorMessage: ''});
    });

    it('returns ok=false and the error message on failure', async () => {
        jest.spyOn(global, 'fetch').mockResolvedValueOnce(
            new Response(JSON.stringify({message: 'Category not found'}), {status: 404})
        );
        const result = await deleteCategory(mockRouter, 99);
        expect(result).toStrictEqual({ok: false, errorMessage: 'Category not found'});
    });

    it('sends a DELETE request', async () => {
        const fetchSpy = jest
            .spyOn(global, 'fetch')
            .mockResolvedValueOnce(new Response('{}', {status: 200}));

        await deleteCategory(mockRouter, 42);

        const options = fetchSpy.mock.calls[0][1] as RequestInit;
        expect(options.method).toBe('DELETE');
    });

    it('includes the X-Requested-With header', async () => {
        const fetchSpy = jest
            .spyOn(global, 'fetch')
            .mockResolvedValueOnce(new Response('{}', {status: 200}));

        await deleteCategory(mockRouter, 42);

        const headers = (fetchSpy.mock.calls[0][1] as RequestInit).headers as string[][];
        expect(headers).toContainEqual(['X-Requested-With', 'XMLHttpRequest']);
    });
});
