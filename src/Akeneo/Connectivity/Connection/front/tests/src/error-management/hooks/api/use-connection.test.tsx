import {renderHook, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useConnection} from '@src/error-management/hooks/api/use-connection';
import {FlowType} from '@src/model/flow-type.enum';
import {mockFetchResponses} from '../../../../test-utils';

// RouterContext default: generate(route, params) = route + '?' + new URLSearchParams(params)
const ROUTE = 'akeneo_connectivity_connection_rest_get?code=erp';

describe('useConnection', () => {
    beforeEach(() => fetchMock.resetMocks());

    it('starts in loading state with no connection', () => {
        mockFetchResponses({
            [ROUTE]: {json: {label: 'ERP', flow_type: FlowType.DATA_SOURCE, auditable: true}},
        });
        const {result} = renderHook(() => useConnection('erp'));
        expect(result.current.loading).toBe(true);
        expect(result.current.connection).toBeUndefined();
    });

    it('returns the connection data after fetch resolves', async () => {
        const connectionData = {label: 'ERP', flow_type: FlowType.DATA_SOURCE, auditable: true};
        mockFetchResponses({[ROUTE]: {json: connectionData}});

        const {result} = renderHook(() => useConnection('erp'));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.connection).toStrictEqual(connectionData);
    });

    it('passes the connection code as a query parameter', async () => {
        const ROUTE_BYNDER = 'akeneo_connectivity_connection_rest_get?code=bynder';
        mockFetchResponses({
            [ROUTE_BYNDER]: {json: {label: 'Bynder', flow_type: FlowType.DATA_DESTINATION, auditable: false}},
        });

        const {result} = renderHook(() => useConnection('bynder'));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.connection?.label).toBe('Bynder');
        expect(result.current.connection?.auditable).toBe(false);
    });
});
