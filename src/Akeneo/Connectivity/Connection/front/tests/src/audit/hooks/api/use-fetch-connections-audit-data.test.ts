import {act, renderHook} from '@testing-library/react';
import React, {PropsWithChildren} from 'react';
import {useFetchConnectionsAuditData} from '@src/audit/hooks/api/use-fetch-connections-audit-data';
import {DashboardProvider} from '@src/audit/dashboard-context';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {fetchResult} from '@src/shared/fetch-result';
import {ok, err} from '@src/shared/fetch-result/result';

jest.mock('@src/shared/router', () => ({
    useRoute: jest.fn(() => '/api/audit'),
}));

jest.mock('@src/shared/fetch-result', () => ({
    fetchResult: jest.fn(),
}));

const mockFetchResult = fetchResult as jest.Mock;

const wrapper = ({children}: PropsWithChildren<{}>) => React.createElement(DashboardProvider, null, children);

describe('useFetchConnectionsAuditData', () => {
    beforeEach(() => {
        mockFetchResult.mockReset();
    });

    it('returns empty object before fetch resolves', () => {
        mockFetchResult.mockReturnValue(new Promise(() => undefined));

        const {result} = renderHook(() => useFetchConnectionsAuditData(AuditEventType.PRODUCT_CREATED), {wrapper});

        expect(result.current).toStrictEqual({});
    });

    it('dispatches fetched data on successful result', async () => {
        const auditData = {magento: {from: '2024-01-01', to: '2024-01-07', daily: {}, weekly_total: 5}};
        mockFetchResult.mockResolvedValue(ok(auditData));

        const {result} = renderHook(() => useFetchConnectionsAuditData(AuditEventType.PRODUCT_CREATED), {wrapper});

        await act(async () => {
            await new Promise(resolve => setTimeout(resolve, 0));
        });

        expect(result.current).toStrictEqual(auditData);
    });

    it('does not dispatch on failed result', async () => {
        mockFetchResult.mockResolvedValue(err(undefined));

        const {result} = renderHook(() => useFetchConnectionsAuditData(AuditEventType.PRODUCT_UPDATED), {wrapper});

        await act(async () => {
            await new Promise(resolve => setTimeout(resolve, 0));
        });

        expect(result.current).toStrictEqual({});
    });

    it('does not dispatch after cleanup (cancellation)', async () => {
        let resolvePromise!: (v: unknown) => void;
        mockFetchResult.mockReturnValue(
            new Promise(resolve => {
                resolvePromise = resolve;
            })
        );

        const {result, unmount} = renderHook(() => useFetchConnectionsAuditData(AuditEventType.PRODUCT_READ), {
            wrapper,
        });

        unmount();
        resolvePromise(ok({magento: {from: '2024-01-01', to: '2024-01-07', daily: {}, weekly_total: 1}}));
        await act(async () => {
            await new Promise(resolve => setTimeout(resolve, 0));
        });

        expect(result.current).toStrictEqual({});
    });

    it('appends event_type param to route', () => {
        mockFetchResult.mockReturnValue(new Promise(() => undefined));

        renderHook(() => useFetchConnectionsAuditData(AuditEventType.PRODUCT_CREATED), {wrapper});

        expect(mockFetchResult).toHaveBeenCalledWith('/api/audit?event_type=product_created');
    });
});
