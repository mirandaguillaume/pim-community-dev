import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {MarketplaceIsLoading} from '@src/connect/components/MarketplaceIsLoading';
import {FeatureFlagsContext} from '@src/shared/feature-flags';
import {renderWithProviders} from '../../../test-utils';

describe('MarketplaceIsLoading', () => {
    it('always renders the extensions section title', () => {
        renderWithProviders(<MarketplaceIsLoading />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.marketplace.extensions.title')
        ).toBeInTheDocument();
    });

    it('does not render the apps section when marketplace_activate flag is disabled', () => {
        renderWithProviders(<MarketplaceIsLoading />);

        expect(
            screen.queryByText('akeneo_connectivity.connection.connect.marketplace.apps.title')
        ).not.toBeInTheDocument();
    });

    it('renders the apps section title when marketplace_activate flag is enabled', () => {
        renderWithProviders(
            <FeatureFlagsContext.Provider value={{isEnabled: () => true}}>
                <MarketplaceIsLoading />
            </FeatureFlagsContext.Provider>
        );

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.apps.title')).toBeInTheDocument();
    });

    it('renders skeleton containers', () => {
        const {container} = renderWithProviders(<MarketplaceIsLoading />);

        expect(container.firstChild).toBeInTheDocument();
    });
});
