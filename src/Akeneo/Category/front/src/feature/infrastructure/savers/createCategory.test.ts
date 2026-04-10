import {createCategory} from './createCategory';
import {Router} from '@akeneo-pim-community/shared';

const mockRouter: Router = {
    generate: jest.fn((route: string) => route),
    redirect: jest.fn(),
};

describe('createCategory', () => {
    beforeEach(() => jest.restoreAllMocks());

    it('returns an empty object on success', async () => {
        jest.spyOn(global, 'fetch').mockResolvedValueOnce(
            new Response('{}', {status: 200})
        );
        const result = await createCategory(mockRouter, 'new_category');
        expect(result).toStrictEqual({});
    });

    it('returns validation errors on failure', async () => {
        const errors = {code: 'This value is already used.'};
        jest.spyOn(global, 'fetch').mockResolvedValueOnce(
            new Response(JSON.stringify(errors), {status: 422})
        );
        const result = await createCategory(mockRouter, 'duplicate');
        expect(result).toStrictEqual(errors);
    });

    it('includes labels when both locale and label are provided', async () => {
        const fetchSpy = jest
            .spyOn(global, 'fetch')
            .mockResolvedValueOnce(new Response('{}', {status: 200}));

        await createCategory(mockRouter, 'shoes', 'master', 'en_US', 'Shoes');

        const body = JSON.parse((fetchSpy.mock.calls[0][1] as RequestInit).body as string);
        expect(body.labels).toStrictEqual({en_US: 'Shoes'});
    });

    it('omits labels when locale or label is missing', async () => {
        const fetchSpy = jest
            .spyOn(global, 'fetch')
            .mockResolvedValueOnce(new Response('{}', {status: 200}));

        await createCategory(mockRouter, 'shoes', 'master');

        const body = JSON.parse((fetchSpy.mock.calls[0][1] as RequestInit).body as string);
        expect(body.labels).toBeUndefined();
    });

    it('sends a POST request with the correct content-type', async () => {
        const fetchSpy = jest
            .spyOn(global, 'fetch')
            .mockResolvedValueOnce(new Response('{}', {status: 200}));

        await createCategory(mockRouter, 'cat');

        const options = fetchSpy.mock.calls[0][1] as RequestInit;
        expect(options.method).toBe('POST');
        expect((options.headers as Record<string, string>)['Content-Type']).toBe(
            'application/x-www-form-urlencoded'
        );
    });
});
