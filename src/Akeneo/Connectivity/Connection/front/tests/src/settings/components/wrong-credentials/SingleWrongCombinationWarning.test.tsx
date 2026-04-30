import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {SingleWrongCombinationWarning} from '@src/settings/components/wrong-credentials/SingleWrongCombinationWarning';
import {renderWithProviders} from '../../../../test-utils';

describe('SingleWrongCombinationWarning', () => {
    it('renders the documentation link', () => {
        renderWithProviders(
            <SingleWrongCombinationWarning
                lastLogin={{username: 'bad_user', date: '2024-01-15T10:30:00+00:00'}}
                goodUsername='good_user'
            />
        );

        expect(screen.getByRole('link')).toBeInTheDocument();
    });

    it('renders the wrong username in the warning text', () => {
        const {container} = renderWithProviders(
            <SingleWrongCombinationWarning
                lastLogin={{username: 'bad_user', date: '2024-01-15T10:30:00+00:00'}}
                goodUsername='good_user'
            />
        );

        expect(container.innerHTML).toContain('bad_user');
    });

    it('renders the good username in the warning text', () => {
        const {container} = renderWithProviders(
            <SingleWrongCombinationWarning
                lastLogin={{username: 'bad_user', date: '2024-01-15T10:30:00+00:00'}}
                goodUsername='good_user'
            />
        );

        expect(container.innerHTML).toContain('good_user');
    });
});
