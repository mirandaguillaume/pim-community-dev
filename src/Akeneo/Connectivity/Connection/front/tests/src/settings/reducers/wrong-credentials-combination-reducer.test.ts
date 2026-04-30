import {reducer, initialState} from '@src/settings/reducers/wrong-credentials-combination-reducer';
import {wrongCredentialsCombinationsFetched} from '@src/settings/actions/wrong-credentials-combinations-actions';
import {WrongCredentialsCombinations} from '@src/model/wrong-credentials-combinations';

type UserTuple = [{username: string; date: string}];

describe('wrong-credentials-combination reducer', () => {
    it('initialState is empty object', () => {
        expect(initialState).toStrictEqual({});
    });

    it('replaces state with payload on WRONG_CREDENTIALS_COMBINATIONS_FETCHED', () => {
        const combinations: WrongCredentialsCombinations = {
            magento: {code: 'magento', users: [{username: 'magento_user', date: '2024-01-01'}] as UserTuple},
            bynder: {code: 'bynder', users: [{username: 'bynder_user', date: '2024-01-02'}] as UserTuple},
        };

        const result = reducer(initialState, wrongCredentialsCombinationsFetched(combinations));

        expect(result).toStrictEqual(combinations);
    });

    it('overwrites previous state entirely', () => {
        const oldState: WrongCredentialsCombinations = {
            magento: {code: 'magento', users: [{username: 'old_user', date: '2023-01-01'}] as UserTuple},
        };
        const newCombinations: WrongCredentialsCombinations = {
            bynder: {code: 'bynder', users: [{username: 'new_user', date: '2024-01-01'}] as UserTuple},
        };

        const result = reducer(oldState, wrongCredentialsCombinationsFetched(newCombinations));

        expect(result).toStrictEqual(newCombinations);
        expect(result).not.toHaveProperty('magento');
    });
});
