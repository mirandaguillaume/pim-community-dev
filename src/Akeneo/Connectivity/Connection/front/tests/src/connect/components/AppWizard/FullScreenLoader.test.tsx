import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {FullScreenLoader} from '@src/connect/components/AppWizard/FullScreenLoader';
import {renderWithProviders} from '../../../../test-utils';

describe('FullScreenLoader', () => {
    it('renders the loader message translation key', () => {
        renderWithProviders(<FullScreenLoader />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.apps.loader.message')).toBeInTheDocument();
    });

    it('renders an image', () => {
        const {container} = renderWithProviders(<FullScreenLoader />);

        expect(container.querySelector('img')).toBeInTheDocument();
    });
});
