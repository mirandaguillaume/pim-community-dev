import {reducer, initialState} from '@src/settings/reducers/wrong-credentials-combination-reducer';
import {wrongCredentialsCombinationsFetched} from '@src/settings/actions/wrong-credentials-combinations-actions';

describe('wrong-credentials-combination reducer', () => {
    it('initialState is empty object', () => {
        expect(initialState).toStrictEqual({});
    });

    it('replaces state with payload on WRONG_CREDENTIALS_COMBINATIONS_FETCHED', () => {
        const combinations = {
            magento: {username: 'magento_user', client_id: 'abc123'},
            bynder: {username: 'bynder_user', client_id: 'def456'},
        };

        const result = reducer(initialState, wrongCredentialsCombinationsFetched(combinations));

        expect(result).toStrictEqual(combinations);
    });

    it('overwrites previous state entirely', () => {
        const oldState = {magento: {username: 'old', client_id: 'old'}};
        const newCombinations = {bynder: {username: 'new', client_id: 'new'}};

        const result = reducer(oldState, wrongCredentialsCombinationsFetched(newCombinations));

        expect(result).toStrictEqual(newCombinations);
        expect(result).not.toHaveProperty('magento');
    });
});
