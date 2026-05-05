import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {CreateCustomAppButton} from '@src/connect/components/CustomApps/CreateCustomAppButton';
import {SecurityContext} from '@src/shared/security';
import {renderWithProviders} from '../../../../test-utils';

const mockLimitReached = jest.fn(() => ({isLoading: false, isError: false, data: false, error: null}));
jest.mock('@src/connect/hooks/use-custom-apps-limit-reached', () => ({
    useCustomAppsLimitReached: () => mockLimitReached(),
}));

describe('CreateCustomAppButton', () => {
    beforeEach(() => {
        mockLimitReached.mockReturnValue({isLoading: false, isError: false, data: false, error: null});
    });

    it('renders the button when permission is granted', () => {
        renderWithProviders(<CreateCustomAppButton />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_button')
        ).toBeInTheDocument();
    });

    it('renders nothing when permission is denied', () => {
        const {container} = renderWithProviders(
            <SecurityContext.Provider value={{isGranted: () => false}}>
                <CreateCustomAppButton />
            </SecurityContext.Provider>
        );

        expect(container).toBeEmptyDOMElement();
    });

    it('disables the button when isLoading is true', () => {
        mockLimitReached.mockReturnValue({isLoading: true, isError: false, data: false, error: null});

        renderWithProviders(<CreateCustomAppButton />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_button').closest('button')
        ).toBeDisabled();
    });

    it('disables the button when the creation limit is reached', () => {
        mockLimitReached.mockReturnValue({isLoading: false, isError: false, data: true, error: null});

        renderWithProviders(<CreateCustomAppButton />);

        expect(
            screen.getByText('akeneo_connectivity.connection.connect.custom_apps.create_button').closest('button')
        ).toBeDisabled();
    });
});
