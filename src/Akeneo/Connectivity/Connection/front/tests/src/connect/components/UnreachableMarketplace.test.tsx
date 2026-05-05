import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {UnreachableMarketplace} from '@src/connect/components/UnreachableMarketplace';
import {renderWithProviders} from '../../../test-utils';

describe('UnreachableMarketplace', () => {
    it('renders the unreachable translation key', () => {
        renderWithProviders(<UnreachableMarketplace />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.marketplace.unreachable')).toBeInTheDocument();
    });

    it('renders an SVG illustration', () => {
        const {container} = renderWithProviders(<UnreachableMarketplace />);

        expect(container.querySelector('svg')).toBeInTheDocument();
    });
});
