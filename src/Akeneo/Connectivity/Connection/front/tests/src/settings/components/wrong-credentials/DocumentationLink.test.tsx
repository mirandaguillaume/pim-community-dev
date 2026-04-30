import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {DocumentationLink} from '@src/settings/components/wrong-credentials/DocumentationLink';
import {renderWithProviders} from '../../../../test-utils';

describe('DocumentationLink', () => {
    it('renders the documentation link with correct href, target, and rel', () => {
        renderWithProviders(<DocumentationLink />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute(
            'href',
            'https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#why-should-you-use-the-connection-username'
        );
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });

    it('renders the link text', () => {
        renderWithProviders(<DocumentationLink />);

        expect(
            screen.getByText(
                'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.link'
            )
        ).toBeInTheDocument();
    });
});
