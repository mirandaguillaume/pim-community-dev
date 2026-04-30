import {
    CONNECTION_DELETED,
    CONNECTION_FETCHED,
    CONNECTION_PASSWORD_REGENERATED,
    CONNECTION_UPDATED,
    CONNECTIONS_FETCHED,
    connectionDeleted,
    connectionFetched,
    connectionPasswordRegenerated,
    connectionsFetched,
    connectionUpdated,
} from '@src/settings/actions/connections-actions';
import {FlowType} from '@src/model/flow-type.enum';

const baseConnection = {
    code: 'magento',
    label: 'Magento',
    flowType: FlowType.DATA_SOURCE,
    image: null,
    auditable: false,
};

const baseCredentials = {
    clientId: 'client-1',
    secret: 'secret-1',
    username: 'user_1',
    password: null,
};

const basePermissions = {
    userRoleId: '1',
    userGroupId: null,
};

describe('connections-actions constants', () => {
    it("CONNECTIONS_FETCHED equals 'CONNECTIONS_FETCHED'", () => {
        expect(CONNECTIONS_FETCHED).toBe('CONNECTIONS_FETCHED');
    });

    it("CONNECTION_FETCHED equals 'CONNECTION_FETCHED'", () => {
        expect(CONNECTION_FETCHED).toBe('CONNECTION_FETCHED');
    });

    it("CONNECTION_UPDATED equals 'CONNECTION_UPDATED'", () => {
        expect(CONNECTION_UPDATED).toBe('CONNECTION_UPDATED');
    });

    it("CONNECTION_DELETED equals 'CONNECTION_DELETED'", () => {
        expect(CONNECTION_DELETED).toBe('CONNECTION_DELETED');
    });

    it("CONNECTION_PASSWORD_REGENERATED equals 'CONNECTION_PASSWORD_REGENERATED'", () => {
        expect(CONNECTION_PASSWORD_REGENERATED).toBe('CONNECTION_PASSWORD_REGENERATED');
    });
});

describe('connections-actions creators', () => {
    it('connectionsFetched wraps an array of connections', () => {
        const payload = [{...baseConnection, ...baseCredentials, ...basePermissions}];
        const action = connectionsFetched(payload);
        expect(action.type).toBe(CONNECTIONS_FETCHED);
        expect(action.payload).toBe(payload);
    });

    it('connectionFetched wraps a full connection+credentials+permissions object', () => {
        const payload = {...baseConnection, ...baseCredentials, ...basePermissions};
        const action = connectionFetched(payload);
        expect(action.type).toBe(CONNECTION_FETCHED);
        expect(action.payload).toBe(payload);
    });

    it('connectionUpdated wraps a connection+permissions object', () => {
        const payload = {...baseConnection, ...basePermissions};
        const action = connectionUpdated(payload);
        expect(action.type).toBe(CONNECTION_UPDATED);
        expect(action.payload).toBe(payload);
    });

    it('connectionDeleted wraps a code string', () => {
        const action = connectionDeleted('magento');
        expect(action.type).toBe(CONNECTION_DELETED);
        expect(action.payload).toBe('magento');
    });

    it('connectionPasswordRegenerated wraps code and password', () => {
        const action = connectionPasswordRegenerated('magento', 'new-pass');
        expect(action.type).toBe(CONNECTION_PASSWORD_REGENERATED);
        expect(action.payload).toStrictEqual({code: 'magento', password: 'new-pass'});
    });
});
