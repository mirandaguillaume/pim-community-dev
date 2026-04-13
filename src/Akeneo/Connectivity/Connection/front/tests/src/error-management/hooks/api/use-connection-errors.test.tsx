import {renderHook, waitFor} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useConnectionErrors} from '@src/error-management/hooks/api/use-connection-errors';
import {mockFetchResponses} from '../../../../test-utils';

const ROUTE = 'akeneo_connectivity_connection_error_management_rest_get_connection_business_errors?connection_code=erp';

describe('useConnectionErrors', () => {
    beforeEach(() => fetchMock.resetMocks());

    it('starts in loading state with empty connectionErrors', () => {
        mockFetchResponses({[ROUTE]: {json: []}});
        const {result} = renderHook(() => useConnectionErrors('erp'));
        expect(result.current.loading).toBe(true);
        expect(result.current.connectionErrors).toHaveLength(0);
    });

    it('returns empty list when the API returns no errors', async () => {
        mockFetchResponses({[ROUTE]: {json: []}});
        const {result} = renderHook(() => useConnectionErrors('erp'));
        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.connectionErrors).toHaveLength(0);
    });

    it('maps raw errors to ConnectionError objects with id and timestamp', async () => {
        const rawErrors = [
            {date_time: '2020-01-01T00:00:00+00:00', content: {message: 'First error', type: 'domain_error'}},
            {date_time: '2020-01-02T00:00:00+00:00', content: {message: 'Second error', type: 'violation_error'}},
        ];
        mockFetchResponses({[ROUTE]: {json: rawErrors}});

        const {result} = renderHook(() => useConnectionErrors('erp'));
        await waitFor(() => expect(result.current.loading).toBe(false));

        const errors = result.current.connectionErrors;
        expect(errors).toHaveLength(2);
        expect(errors[0].id).toBe(0);
        expect(errors[0].timestamp).toBe(Date.parse('2020-01-01T00:00:00+00:00'));
        expect(errors[1].id).toBe(1);
        expect(errors[1].timestamp).toBe(Date.parse('2020-01-02T00:00:00+00:00'));
    });

    it('assigns sequential ids starting from 0', async () => {
        const rawErrors = Array.from({length: 3}, (_, i) => ({
            date_time: `2020-01-0${i + 1}T00:00:00+00:00`,
            content: {message: `Error ${i}`, type: 'domain_error' as const},
        }));
        mockFetchResponses({[ROUTE]: {json: rawErrors}});

        const {result} = renderHook(() => useConnectionErrors('erp'));
        await waitFor(() => expect(result.current.loading).toBe(false));

        result.current.connectionErrors.forEach((error, index) => {
            expect(error.id).toBe(index);
        });
    });
});
