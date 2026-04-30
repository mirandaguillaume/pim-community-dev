import {useCheckReachability} from '@src/webhook/hooks/api/use-webhook-check-reachability';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn((route: string) => `/api/${route}`);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(RouterContext.Provider, {value: {generate, redirect: jest.fn()}}, children);

describe('useCheckReachability', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('sends a POST request with url and secret', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({success: true, message: 'OK'}), {status: 200});

        const {result} = renderHook(() => useCheckReachability('magento'), {wrapper});
        const checkReachability = result.current;

        const response = await checkReachability('https://example.com', 'my-secret');

        expect(fetchMock).toHaveBeenCalledWith(
            expect.any(String),
            expect.objectContaining({
                method: 'POST',
                body: JSON.stringify({url: 'https://example.com', secret: 'my-secret'}),
            })
        );
        expect(response).toMatchObject({value: {success: true, message: 'OK'}});
    });

    it('returns an err result on 4xx response', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({success: false, message: 'Unreachable'}), {status: 400});

        const {result} = renderHook(() => useCheckReachability('magento'), {wrapper});
        const checkReachability = result.current;

        const response = await checkReachability('https://bad.url', 'secret');

        expect(response).toMatchObject({error: {success: false, message: 'Unreachable'}});
    });
});
