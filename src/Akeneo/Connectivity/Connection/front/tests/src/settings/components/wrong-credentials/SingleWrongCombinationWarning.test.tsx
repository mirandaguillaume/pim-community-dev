import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {SingleWrongCombinationWarning} from '@src/settings/components/wrong-credentials/SingleWrongCombinationWarning';
import {renderWithProviders} from '../../../../test-utils';
import {TranslateContext} from '@src/shared/translate';

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

    it('renders a username received from the API as text, not as markup', () => {
        const interpolate = (_id: string, placeholders?: {[name: string]: string}) =>
            `${placeholders?.wrong_username} / ${placeholders?.good_username}`;

        const {container} = renderWithProviders(
            <TranslateContext.Provider value={interpolate}>
                <SingleWrongCombinationWarning
                    lastLogin={{username: '<img src=x onerror=alert(1)>', date: '2024-01-15T10:30:00+00:00'}}
                    goodUsername='good_user'
                />
            </TranslateContext.Provider>
        );

        expect(container.querySelector('img')).toBeNull();
        expect(container.textContent).toContain('<img src=x onerror=alert(1)>');
    });
});
