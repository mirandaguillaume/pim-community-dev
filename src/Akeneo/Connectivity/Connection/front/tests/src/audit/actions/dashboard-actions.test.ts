import {
    connectionsFetched,
    CONNECTIONS_FETCHED,
    connectionsAuditDataFetched,
    CONNECTIONS_AUDIT_DATA_FETCHED,
} from '@src/audit/actions/dashboard-actions';
import {AuditEventType} from '@src/model/audit-event-type.enum';
import {FlowType} from '@src/model/flow-type.enum';

describe('dashboard action type constants', () => {
    it("CONNECTIONS_FETCHED is 'CONNECTIONS_FETCHED'", () => {
        expect(CONNECTIONS_FETCHED).toBe('CONNECTIONS_FETCHED');
    });
    it("CONNECTIONS_AUDIT_DATA_FETCHED is 'CONNECTIONS_AUDIT_DATA_FETCHED'", () => {
        expect(CONNECTIONS_AUDIT_DATA_FETCHED).toBe('CONNECTIONS_AUDIT_DATA_FETCHED');
    });
});

describe('connectionsFetched', () => {
    it('creates an action with the given connections as payload', () => {
        const connections = [{code: 'erp', label: 'ERP', flowType: FlowType.DATA_SOURCE, image: null, auditable: true}];
        expect(connectionsFetched(connections)).toStrictEqual({
            type: CONNECTIONS_FETCHED,
            payload: connections,
        });
    });

    it('accepts an empty list', () => {
        const action = connectionsFetched([]);
        expect(action.type).toBe(CONNECTIONS_FETCHED);
        expect(action.payload).toHaveLength(0);
    });
});

describe('connectionsAuditDataFetched', () => {
    it('creates an action with eventType and data in payload', () => {
        const data = {erp: {daily: {'2020-01-01': 3}, weekly_total: 3}};
        expect(connectionsAuditDataFetched(AuditEventType.PRODUCT_CREATED, data)).toStrictEqual({
            type: CONNECTIONS_AUDIT_DATA_FETCHED,
            payload: {eventType: AuditEventType.PRODUCT_CREATED, data},
        });
    });

    it('works for all three audit event types', () => {
        const data = {};
        [AuditEventType.PRODUCT_CREATED, AuditEventType.PRODUCT_UPDATED, AuditEventType.PRODUCT_READ].forEach(
            eventType => {
                const action = connectionsAuditDataFetched(eventType, data);
                expect(action.payload.eventType).toBe(eventType);
                expect(action.payload.data).toBe(data);
            }
        );
    });
});
