import {reducer, initialState} from '@src/settings/reducers/connections-reducer';
import {
    connectionsFetched,
    connectionFetched,
    connectionUpdated,
    connectionDeleted,
    connectionPasswordRegenerated,
} from '@src/settings/actions/connections-actions';
import {FlowType} from '@src/model/flow-type.enum';

const baseConnection = {
    code: 'magento',
    label: 'Magento',
    flowType: FlowType.DATA_SOURCE,
    image: null,
    auditable: false,
};

const fullConnection = {
    ...baseConnection,
    clientId: 'client_id',
    secret: 'secret',
    username: 'magento_app',
    password: 'pass123',
    userRoleId: 'role_1',
    userGroupId: null,
};

describe('connections-reducer', () => {
    it('starts with an empty initial state', () => {
        expect(initialState).toEqual({});
    });

    it('populates state with defaults on CONNECTIONS_FETCHED', () => {
        const state = reducer({}, connectionsFetched([baseConnection]));

        expect(state.magento).toMatchObject({
            code: 'magento',
            label: 'Magento',
            clientId: '',
            secret: '',
            username: '',
            password: null,
            userRoleId: '',
            userGroupId: null,
        });
    });

    it('merges full payload on CONNECTION_FETCHED', () => {
        const state = reducer({}, connectionFetched(fullConnection));

        expect(state.magento).toMatchObject(fullConnection);
    });

    it('preserves existing password when CONNECTION_FETCHED payload password is null', () => {
        const existing = {...fullConnection, password: 'existing_pass'};
        const state = reducer({magento: existing}, connectionFetched({...fullConnection, password: null}));

        expect(state.magento.password).toBe('existing_pass');
    });

    it('merges payload into existing entry on CONNECTION_UPDATED', () => {
        const state = reducer(
            {magento: fullConnection},
            connectionUpdated({...baseConnection, label: 'Magento Updated', userRoleId: 'role_2', userGroupId: null})
        );

        expect(state.magento.label).toBe('Magento Updated');
        expect(state.magento.clientId).toBe('client_id');
    });

    it('removes the connection on CONNECTION_DELETED', () => {
        const state = reducer({magento: fullConnection}, connectionDeleted('magento'));

        expect(state.magento).toBeUndefined();
    });

    it('updates only the password on CONNECTION_PASSWORD_REGENERATED', () => {
        const state = reducer({magento: fullConnection}, connectionPasswordRegenerated('magento', 'new_password'));

        expect(state.magento.password).toBe('new_password');
        expect(state.magento.label).toBe('Magento');
    });
});
