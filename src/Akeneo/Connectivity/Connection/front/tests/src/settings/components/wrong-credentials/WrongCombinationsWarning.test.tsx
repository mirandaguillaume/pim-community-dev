import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {WrongCombinationsWarning} from '@src/settings/components/wrong-credentials/WrongCombinationsWarning';
import {renderWithProviders} from '../../../../test-utils';

const singleUserCombination = {
    code: 'magento',
    users: [{username: 'bad_user', date: '2024-01-15T10:30:00+00:00'}],
} as any;

const multiUserCombination = {
    code: 'magento',
    users: [
        {username: 'bad_user_1', date: '2024-01-15T10:30:00+00:00'},
        {username: 'bad_user_2', date: '2024-01-14T09:00:00+00:00'},
    ],
} as any;

describe('WrongCombinationsWarning', () => {
    it('renders the documentation link for a single user warning', () => {
        renderWithProviders(<WrongCombinationsWarning username='good_user' wrongCombination={singleUserCombination} />);

        expect(screen.getByRole('link')).toBeInTheDocument();
    });

    it('renders the list title when multiple users have wrong combinations', () => {
        renderWithProviders(<WrongCombinationsWarning username='good_user' wrongCombination={multiUserCombination} />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.list'
            )
        ).toBeInTheDocument();
    });

    it('does not render the list title for a single user warning', () => {
        renderWithProviders(<WrongCombinationsWarning username='good_user' wrongCombination={singleUserCombination} />);

        expect(
            screen.queryByText(
                'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.list'
            )
        ).not.toBeInTheDocument();
    });
});
