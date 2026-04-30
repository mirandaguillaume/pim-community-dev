import {useDeleteConnection} from '@src/settings/api-hooks/use-delete-connection';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';
import fetchMock from 'jest-fetch-mock';
import {isOk, isErr} from '@src/shared/fetch-result/result';
import {mockFetchResponses} from '../../../test-utils';

const notify = jest.fn();

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(NotifyContext.Provider, {value: notify}, children);

beforeEach(() => {
    fetchMock.resetMocks();
    notify.mockReset();
});

describe('useDeleteConnection', () => {
    it('sends DELETE to the correct route and notifies success', async () => {
        mockFetchResponses({
            'akeneo_connectivity_connection_rest_delete?code=my-conn': {
                json: '',
                status: 204,
            },
        });

        const {result} = renderHook(() => useDeleteConnection('my-conn'), {wrapper});
        const response = await result.current();

        expect(fetchMock).toHaveBeenCalledWith(
            'akeneo_connectivity_connection_rest_delete?code=my-conn',
            expect.objectContaining({method: 'DELETE'})
        );
        expect(isOk(response)).toBe(true);
        expect(notify).toHaveBeenCalledWith(
            NotificationLevel.SUCCESS,
            expect.stringContaining('delete_connection.flash.success')
        );
    });

    it('notifies error and returns err result when request fails', async () => {
        mockFetchResponses({
            'akeneo_connectivity_connection_rest_delete?code=bad-conn': {
                json: {message: 'Connection not found'},
                status: 404,
            },
        });

        const {result} = renderHook(() => useDeleteConnection('bad-conn'), {wrapper});
        const response = await result.current();

        expect(isErr(response)).toBe(true);
        expect(notify).toHaveBeenCalledWith(NotificationLevel.ERROR, 'Connection not found');
    });
});
