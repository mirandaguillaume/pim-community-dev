import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {MarketplaceHelper} from '@src/connect/components/MarketplaceHelper';
import {FeatureFlagsContext} from '@src/shared/feature-flags';
import {renderWithProviders} from '../../../test-utils';

jest.mock('@src/connect/hooks/use-fetch-marketplace-url', () => ({
    useFetchMarketplaceUrl: () => jest.fn().mockResolvedValue('https://marketplace.example.com'),
}));

describe('MarketplaceHelper', () => {
    it('renders the title_without_apps key when feature flag is disabled', () => {
        const {container} = renderWithProviders(<MarketplaceHelper count={5} />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.marketplace.helper.title_without_apps'
        );
    });

    it('renders the title key when feature flag is enabled', () => {
        const {container} = renderWithProviders(
            <FeatureFlagsContext.Provider value={{isEnabled: () => true}}>
                <MarketplaceHelper count={5} />
            </FeatureFlagsContext.Provider>
        );

        expect(container.textContent).toContain('akeneo_connectivity.connection.connect.marketplace.helper.title');
    });

    it('renders the description key', () => {
        renderWithProviders(<MarketplaceHelper count={0} />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.helper.description')
        ).toBeInTheDocument();
    });

    it('renders the link key', () => {
        renderWithProviders(<MarketplaceHelper count={0} />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.helper.link')).toBeInTheDocument();
    });
});
