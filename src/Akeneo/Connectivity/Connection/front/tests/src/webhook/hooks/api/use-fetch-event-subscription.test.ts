import {useFetchEventSubscription} from '@src/webhook/hooks/api/use-fetch-event-subscription';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook, act} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn((route: string) => `/api/${route}`);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(RouterContext.Provider, {value: {generate, redirect: jest.fn()}}, children);

const eventSubscription = {
    connectionCode: 'magento',
    enabled: true,
    isUsingUuid: false,
    secret: 'abc123',
    url: 'https://example.com/webhook',
};

const subscriptionsLimit = {limit: 10, current: 3};

describe('useFetchEventSubscription', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('starts with undefined eventSubscription and eventSubscriptionsLimit', () => {
        const {result} = renderHook(() => useFetchEventSubscription('magento'), {wrapper});

        expect(result.current.eventSubscription).toBeUndefined();
        expect(result.current.eventSubscriptionsLimit).toBeUndefined();
    });

    it('populates state after fetchEventSubscription is called', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify({
                event_subscription: eventSubscription,
                active_event_subscriptions_limit: subscriptionsLimit,
            }),
            {status: 200}
        );

        const {result} = renderHook(() => useFetchEventSubscription('magento'), {wrapper});

        await act(async () => {
            result.current.fetchEventSubscription();
            await new Promise(r => setTimeout(r, 0));
        });

        expect(result.current.eventSubscription).toStrictEqual(eventSubscription);
        expect(result.current.eventSubscriptionsLimit).toStrictEqual(subscriptionsLimit);
    });

    it('throws when the API returns an error response', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({error: 'not found'}), {status: 404});

        const {result} = renderHook(() => useFetchEventSubscription('unknown'), {wrapper});

        await expect(
            act(async () => {
                result.current.fetchEventSubscription();
                await new Promise(r => setTimeout(r, 0));
            })
        ).rejects.toThrow("Webhook for connection 'unknown' not found.");
    });
});
