import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {CustomAppCard} from '@src/connect/components/CustomApps/CustomAppCard';
import {CustomApp} from '@src/model/app';
import {SecurityContext} from '@src/shared/security';
import {renderWithProviders} from '../../../../test-utils';

const baseApp: CustomApp = {
    id: 'custom-1',
    name: 'My Custom App',
    logo: null,
    author: 'Dev User',
    url: null,
    activate_url: 'https://example.com/activate',
    callback_url: 'https://example.com/callback',
    connected: false,
};

describe('CustomAppCard', () => {
    it('renders the app name', () => {
        renderWithProviders(<CustomAppCard customApp={baseApp} />);

        expect(screen.getByText('My Custom App')).toBeInTheDocument();
    });

    it('renders the author via translate', () => {
        const {container} = renderWithProviders(<CustomAppCard customApp={baseApp} />);

        expect(container.textContent).toContain('akeneo_connectivity.connection.connect.marketplace.card.developed_by');
    });

    it('renders the fallback author key when author is null', () => {
        const {container} = renderWithProviders(<CustomAppCard customApp={{...baseApp, author: null}} />);

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.connect.marketplace.custom_apps.removed_user'
        );
    });

    it('renders the delete button when permission is granted', () => {
        renderWithProviders(<CustomAppCard customApp={baseApp} />);

        expect(screen.getByTitle('pim_common.delete')).toBeInTheDocument();
    });

    it('does not render the delete button when permission is denied', () => {
        renderWithProviders(
            <SecurityContext.Provider value={{isGranted: () => false}}>
                <CustomAppCard customApp={baseApp} />
            </SecurityContext.Provider>
        );

        expect(screen.queryByTitle('pim_common.delete')).not.toBeInTheDocument();
    });

    it('renders additional actions when provided', () => {
        renderWithProviders(
            <CustomAppCard customApp={baseApp} additionalActions={[<button key='action'>Custom Action</button>]} />
        );

        expect(screen.getByText('Custom Action')).toBeInTheDocument();
    });
});
