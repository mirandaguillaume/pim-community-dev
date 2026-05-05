import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ConnectedAppsContainerIsLoading} from '@src/connect/components/ConnectedApps/ConnectedAppsContainerIsLoading';
import {renderWithProviders} from '../../../../test-utils';

describe('ConnectedAppsContainerIsLoading', () => {
    it('renders the apps section title', () => {
        renderWithProviders(<ConnectedAppsContainerIsLoading />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.list.apps.title')
        ).toBeInTheDocument();
    });

    it('renders skeleton containers', () => {
        const {container} = renderWithProviders(<ConnectedAppsContainerIsLoading />);

        expect(container.firstChild).toBeInTheDocument();
    });
});
