import {
    WRONG_CREDENTIALS_COMBINATIONS_FETCHED,
    wrongCredentialsCombinationsFetched,
} from '@src/settings/actions/wrong-credentials-combinations-actions';
import {WrongCredentialsCombinations} from '@src/model/wrong-credentials-combinations';

describe('wrong-credentials-combinations-actions', () => {
    it("WRONG_CREDENTIALS_COMBINATIONS_FETCHED is 'WRONG_CREDENTIALS_COMBINATIONS_FETCHED'", () => {
        expect(WRONG_CREDENTIALS_COMBINATIONS_FETCHED).toBe('WRONG_CREDENTIALS_COMBINATIONS_FETCHED');
    });

    it('wrongCredentialsCombinationsFetched creates action with payload', () => {
        const combinations: WrongCredentialsCombinations = {
            magento: {
                code: 'magento',
                users: [{username: 'wrong_user', date: '2024-01-01'}] as [{username: string; date: string}],
            },
        };
        const action = wrongCredentialsCombinationsFetched(combinations);

        expect(action.type).toBe(WRONG_CREDENTIALS_COMBINATIONS_FETCHED);
        expect(action.payload).toStrictEqual(combinations);
    });
});
