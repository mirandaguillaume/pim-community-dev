import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {MarketplaceCard} from '@src/connect/components/MarketplaceCard';
import {Extension} from '@src/model/extension';
import {renderWithProviders} from '../../../test-utils';

const baseExtension: Extension = {
    id: 'ext-1',
    name: 'My Extension',
    logo: 'https://example.com/logo.png',
    author: 'Acme Corp',
    partner: null,
    description: 'A short description.',
    url: 'https://example.com',
    categories: ['ecommerce'],
    certified: false,
};

describe('MarketplaceCard', () => {
    it('renders the extension name', () => {
        renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(screen.getByText('My Extension')).toBeInTheDocument();
    });

    it('renders the logo image', () => {
        renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(screen.getByAltText('My Extension')).toBeInTheDocument();
    });

    it('renders the author via translate', () => {
        const {container} = renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(container.textContent).toContain('akeneo_connectivity.connection.connect.marketplace.card.developed_by');
    });

    it('renders the short description', () => {
        renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(screen.getByText('A short description.')).toBeInTheDocument();
    });

    it('renders a read_more link when description exceeds 150 characters', () => {
        const longDescription = 'A'.repeat(151);
        renderWithProviders(<MarketplaceCard item={{...baseExtension, description: longDescription}} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.read_more')
        ).toBeInTheDocument();
    });

    it('renders the more info button', () => {
        renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.card.more_info')
        ).toBeInTheDocument();
    });

    it('renders the partner tag when partner is set', () => {
        renderWithProviders(<MarketplaceCard item={{...baseExtension, partner: 'Silver Partner'}} />);

        expect(screen.getByText('Silver Partner')).toBeInTheDocument();
    });

    it('renders the first category tag', () => {
        renderWithProviders(<MarketplaceCard item={baseExtension} />);

        expect(screen.getByText('ecommerce')).toBeInTheDocument();
    });
});
