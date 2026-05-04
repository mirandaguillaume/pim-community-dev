import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {AuthorizeClientError} from '@src/connect/components/AuthorizeClientError';
import {renderWithProviders} from '../../../test-utils';

describe('AuthorizeClientError', () => {
    it('renders the error prop as translated text', () => {
        renderWithProviders(
            <AuthorizeClientError error='akeneo_connectivity.connection.connect.apps.error.app_not_found' />
        );

        expect(screen.getByText('akeneo_connectivity.connection.connect.apps.error.app_not_found')).toBeInTheDocument();
    });

    it('renders the sub_text translation key', () => {
        renderWithProviders(<AuthorizeClientError error='some.error.key' />);

        expect(screen.getByText('akeneo_connectivity.connection.connect.apps.error.sub_text')).toBeInTheDocument();
    });
});
