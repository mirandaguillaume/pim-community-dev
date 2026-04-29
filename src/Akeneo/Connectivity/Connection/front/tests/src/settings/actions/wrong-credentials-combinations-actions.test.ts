import {
    WRONG_CREDENTIALS_COMBINATIONS_FETCHED,
    wrongCredentialsCombinationsFetched,
} from '@src/settings/actions/wrong-credentials-combinations-actions';

describe('wrong-credentials-combinations-actions', () => {
    it("WRONG_CREDENTIALS_COMBINATIONS_FETCHED is 'WRONG_CREDENTIALS_COMBINATIONS_FETCHED'", () => {
        expect(WRONG_CREDENTIALS_COMBINATIONS_FETCHED).toBe('WRONG_CREDENTIALS_COMBINATIONS_FETCHED');
    });

    it('wrongCredentialsCombinationsFetched creates action with payload', () => {
        const combinations = {
            magento: {code: 'magento', users: [{username: 'wrong_user', date: '2024-01-01'}]},
        };
        const action = wrongCredentialsCombinationsFetched(combinations);

        expect(action.type).toBe(WRONG_CREDENTIALS_COMBINATIONS_FETCHED);
        expect(action.payload).toStrictEqual(combinations);
    });
});
