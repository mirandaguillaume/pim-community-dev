import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {CopiableCredential} from '@src/settings/components/credentials/CopiableCredential';
import {renderWithProviders} from '../../../../test-utils';

describe('CopiableCredential', () => {
    it('renders the label and value', () => {
        renderWithProviders(<CopiableCredential label='Client ID'>client_id_value</CopiableCredential>);

        expect(screen.getByText('Client ID')).toBeInTheDocument();
        expect(screen.getByText('client_id_value')).toBeInTheDocument();
    });

    it('renders a copy button', () => {
        renderWithProviders(<CopiableCredential label='Secret'>some_secret</CopiableCredential>);

        expect(
            screen.getByTitle('akeneo_connectivity.connection.edit_connection.credentials.action.copy')
        ).toBeInTheDocument();
    });

    it('renders additional actions alongside the copy button', () => {
        renderWithProviders(
            <CopiableCredential label='Password' actions={<button>Regenerate</button>}>
                pwd
            </CopiableCredential>
        );

        expect(screen.getByRole('button', {name: 'Regenerate'})).toBeInTheDocument();
    });

    it('renders the helper when provided', () => {
        renderWithProviders(
            <CopiableCredential label='URL' helper={<span>Helper info</span>}>
                some_url
            </CopiableCredential>
        );

        expect(screen.getByText('Helper info')).toBeInTheDocument();
    });
});
