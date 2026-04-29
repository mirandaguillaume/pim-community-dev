import {renderHook, act} from '@testing-library/react';
import {useQuery} from '@src/shared/fetch/hooks/use-query';
import {BadRequestError} from '@src/shared/fetch/errors/bad-request-error';
import {NotFoundError} from '@src/shared/fetch/errors/not-found-error';
import {UnauthorizedError} from '@src/shared/fetch/errors/unauthorized-error';

jest.mock('@src/shared/router', () => ({
    useRoute: () => '/api/mock-route',
}));

const originalConsoleError = console.error;
beforeAll(() => {
    // Suppress React error boundary noise from expected thrown errors
    console.error = jest.fn();
});
afterAll(() => {
    console.error = originalConsoleError;
});

describe('useQuery', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('returns loading state initially', () => {
        fetchMock.mockResponseOnce(JSON.stringify({id: 1}));
        const {result} = renderHook(() => useQuery('some_route'));
        expect(result.current.loading).toBe(true);
        expect(result.current.data).toBeUndefined();
    });

    it('returns data on successful fetch', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({id: 1, name: 'test'}));

        const {result} = renderHook(() => useQuery<{id: number; name: string}>('some_route'));

        await act(async () => {
            await new Promise(r => setTimeout(r, 0));
        });

        expect(result.current.loading).toBe(false);
        expect(result.current.data).toStrictEqual({id: 1, name: 'test'});
    });

    it('fetches with credentials and json content-type', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({}));
        renderHook(() => useQuery('some_route'));

        await act(async () => {
            await new Promise(r => setTimeout(r, 0));
        });

        expect(fetchMock).toHaveBeenCalledWith('/api/mock-route', {
            method: 'GET',
            credentials: 'include',
            headers: {'content-type': 'application/json'},
        });
    });

    it('throws BadRequestError on 400 response', async () => {
        const errorBody = {violations: ['Field required']};
        fetchMock.mockResponseOnce(JSON.stringify(errorBody), {status: 400});

        const {result} = renderHook(() => useQuery('some_route'));

        await expect(
            act(async () => {
                await new Promise(r => setTimeout(r, 0));
            })
        ).rejects.toBeInstanceOf(BadRequestError);
    });

    it('throws UnauthorizedError on 401 response', async () => {
        fetchMock.mockResponseOnce('', {status: 401});

        const {result} = renderHook(() => useQuery('some_route'));

        await expect(
            act(async () => {
                await new Promise(r => setTimeout(r, 0));
            })
        ).rejects.toBeInstanceOf(UnauthorizedError);
    });

    it('throws NotFoundError on 404 response', async () => {
        fetchMock.mockResponseOnce('', {status: 404});

        const {result} = renderHook(() => useQuery('some_route'));

        await expect(
            act(async () => {
                await new Promise(r => setTimeout(r, 0));
            })
        ).rejects.toBeInstanceOf(NotFoundError);
    });
});
