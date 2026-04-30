import {useFetchEventSubscription} from '@src/webhook/hooks/api/use-fetch-event-subscription';
import {fetchResult} from '@src/shared/fetch-result';
import {ok, err} from '@src/shared/fetch-result/result';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook, act} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

jest.mock('@src/shared/fetch-result', () => ({
    fetchResult: jest.fn(),
}));

const mockFetchResult = fetchResult as jest.Mock;
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
    afterEach(() => {
        jest.clearAllMocks();
    });

    it('starts with undefined eventSubscription and eventSubscriptionsLimit', () => {
        const {result} = renderHook(() => useFetchEventSubscription('magento'), {wrapper});

        expect(result.current.eventSubscription).toBeUndefined();
        expect(result.current.eventSubscriptionsLimit).toBeUndefined();
    });

    it('populates state after fetchEventSubscription is called', async () => {
        mockFetchResult.mockResolvedValue(
            ok({
                event_subscription: eventSubscription,
                active_event_subscriptions_limit: subscriptionsLimit,
            })
        );

        const {result} = renderHook(() => useFetchEventSubscription('magento'), {wrapper});

        await act(async () => {
            result.current.fetchEventSubscription();
            await new Promise(r => setTimeout(r, 0));
        });

        expect(result.current.eventSubscription).toStrictEqual(eventSubscription);
        expect(result.current.eventSubscriptionsLimit).toStrictEqual(subscriptionsLimit);
    });

    it('passes the connection code as a route param', async () => {
        mockFetchResult.mockResolvedValue(
            ok({
                event_subscription: eventSubscription,
                active_event_subscriptions_limit: subscriptionsLimit,
            })
        );

        renderHook(() => useFetchEventSubscription('bynder'), {wrapper});

        expect(generate).toHaveBeenCalledWith(
            'akeneo_connectivity_connection_webhook_rest_get',
            {code: 'bynder'}
        );
    });

    it('returns a stable fetchEventSubscription callback across re-renders', () => {
        mockFetchResult.mockResolvedValue(err(null));

        const {result, rerender} = renderHook(() => useFetchEventSubscription('magento'), {wrapper});
        const first = result.current.fetchEventSubscription;
        rerender();
        expect(result.current.fetchEventSubscription).toBe(first);
    });
});
