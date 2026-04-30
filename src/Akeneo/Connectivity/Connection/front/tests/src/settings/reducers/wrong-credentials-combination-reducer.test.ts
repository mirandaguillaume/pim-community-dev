import {
    wrongCredentialsCombinationsFetched,
    WRONG_CREDENTIALS_COMBINATIONS_FETCHED,
} from '@src/settings/actions/wrong-credentials-combinations-actions';
import {initialState, reducer} from '@src/settings/reducers/wrong-credentials-combination-reducer';

const aCombination = {code: 'erp', users: [{username: 'bad_user', date: '2024-01-15'}]} as any;

describe('wrongCredentialsCombinationsFetched', () => {
    it('creates an action with the correct type and payload', () => {
        const payload = {erp: aCombination};
        const action = wrongCredentialsCombinationsFetched(payload);

        expect(action.type).toBe(WRONG_CREDENTIALS_COMBINATIONS_FETCHED);
        expect(action.payload).toBe(payload);
    });
});

describe('wrong-credentials-combination reducer', () => {
    it('initial state is an empty object', () => {
        expect(initialState).toEqual({});
    });

    it('replaces state on WRONG_CREDENTIALS_COMBINATIONS_FETCHED', () => {
        const combinations = {erp: aCombination};
        const state = reducer({}, wrongCredentialsCombinationsFetched(combinations));

        expect(state).toBe(combinations);
    });
});
