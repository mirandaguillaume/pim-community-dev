import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Credential} from '@src/settings/components/credentials/Credential';
import {renderWithProviders} from '../../../../test-utils';

describe('Credential', () => {
    it('renders the label and value', () => {
        renderWithProviders(
            <Credential label='Client ID' actions={null}>
                client_id_value
            </Credential>
        );

        expect(screen.getByText('Client ID')).toBeInTheDocument();
        expect(screen.getByText('client_id_value')).toBeInTheDocument();
    });

    it('renders actions', () => {
        renderWithProviders(
            <Credential label='Secret' actions={<button>Copy</button>}>
                some_value
            </Credential>
        );

        expect(screen.getByRole('button', {name: 'Copy'})).toBeInTheDocument();
    });

    it('renders the helper when provided', () => {
        renderWithProviders(
            <Credential label='URL' actions={null} helper={<span>Helper text</span>}>
                value
            </Credential>
        );

        expect(screen.getByText('Helper text')).toBeInTheDocument();
    });
});
