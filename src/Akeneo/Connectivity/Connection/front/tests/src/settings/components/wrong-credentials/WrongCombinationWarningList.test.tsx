import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {WrongCombinationWarningList} from '@src/settings/components/wrong-credentials/WrongCombinationWarningList';
import {renderWithProviders} from '../../../../test-utils';

const combination = {
    code: 'magento',
    users: [
        {username: 'bad_user_1', date: '2024-01-15T10:30:00+00:00'},
        {username: 'bad_user_2', date: '2024-01-14T09:00:00+00:00'},
    ],
} as any;

describe('WrongCombinationWarningList', () => {
    it('renders the list title', () => {
        renderWithProviders(<WrongCombinationWarningList combinations={combination} goodUsername='good_user' />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.list'
            )
        ).toBeInTheDocument();
    });

    it('renders a row for each wrong user', () => {
        const {container} = renderWithProviders(
            <WrongCombinationWarningList combinations={combination} goodUsername='good_user' />
        );

        expect(container.innerHTML).toContain('bad_user_1');
        expect(container.innerHTML).toContain('bad_user_2');
    });

    it('renders the documentation link', () => {
        renderWithProviders(<WrongCombinationWarningList combinations={combination} goodUsername='good_user' />);

        expect(screen.getByRole('link')).toBeInTheDocument();
    });
});
