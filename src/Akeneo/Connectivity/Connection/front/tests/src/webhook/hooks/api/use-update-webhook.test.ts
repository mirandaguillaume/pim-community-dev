import {useUpdateWebhook} from '@src/webhook/hooks/api/use-update-webhook';
import {RouterContext} from '@src/shared/router/router-context';
import {NotifyContext} from '@src/shared/notify/notify-context';
import {TranslateContext} from '@src/shared/translate/translate-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn((route: string) => `/api/${route}`);
const notify = jest.fn();
const translate = jest.fn((key: string) => key);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(
        RouterContext.Provider,
        {value: {generate, redirect: jest.fn()}},
        React.createElement(
            NotifyContext.Provider,
            {value: notify},
            React.createElement(TranslateContext.Provider, {value: translate}, children)
        )
    );

const requestData = {
    connectionCode: 'magento',
    enabled: true,
    url: 'https://example.com/webhook',
    isUsingUuid: false,
};

describe('useUpdateWebhook', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
        jest.clearAllMocks();
    });

    it('notifies success when the API call succeeds', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify({
                connectionCode: 'magento',
                url: 'https://example.com/webhook',
                secret: null,
                enabled: true,
                isUsingUuid: false,
            }),
            {status: 200}
        );

        const {result} = renderHook(() => useUpdateWebhook('magento'), {wrapper});

        await result.current(requestData);

        expect(notify).toHaveBeenCalledWith('success', 'akeneo_connectivity.connection.webhook.flash.success');
    });

    it('notifies error with translated generic message when API returns error without field errors', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({message: 'Something went wrong', errors: null}), {status: 400});

        const {result} = renderHook(() => useUpdateWebhook('magento'), {wrapper});

        await result.current(requestData);

        expect(notify).toHaveBeenCalledWith('error', 'akeneo_connectivity.connection.webhook.flash.error');
    });

    it('notifies once per field error when API returns per-field errors', async () => {
        fetchMock.mockResponseOnce(
            JSON.stringify({
                message: 'Validation error',
                errors: [
                    {field: 'url', message: 'akeneo_connectivity.connection.webhook.error.url_invalid'},
                    {field: 'enabled', message: 'akeneo_connectivity.connection.webhook.error.enabled_required'},
                ],
            }),
            {status: 422}
        );

        const {result} = renderHook(() => useUpdateWebhook('magento'), {wrapper});

        await result.current(requestData);

        expect(notify).toHaveBeenCalledTimes(2);
        expect(notify).toHaveBeenCalledWith('error', 'akeneo_connectivity.connection.webhook.error.url_invalid');
        expect(notify).toHaveBeenCalledWith('error', 'akeneo_connectivity.connection.webhook.error.enabled_required');
    });

    it('returns an ok result on success', async () => {
        const okData = {
            connectionCode: 'magento',
            url: 'https://example.com',
            secret: 'sec',
            enabled: true,
            isUsingUuid: false,
        };
        fetchMock.mockResponseOnce(JSON.stringify(okData), {status: 200});

        const {result} = renderHook(() => useUpdateWebhook('magento'), {wrapper});

        const response = await result.current(requestData);

        expect(response).toMatchObject({value: okData});
    });
});
