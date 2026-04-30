import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {DownloadLogsButton} from '@src/webhook/components/DownloadLogsButton';
import {renderWithProviders} from '../../../test-utils';

const enabledSubscription = {
    connectionCode: 'magento',
    enabled: true,
    isUsingUuid: false,
    secret: null,
    url: 'https://example.com/webhook',
};

const disabledSubscription = {
    ...enabledSubscription,
    enabled: false,
};

describe('DownloadLogsButton', () => {
    it('renders the download logs label', () => {
        renderWithProviders(<DownloadLogsButton />);

        expect(screen.getByText('akeneo_connectivity.connection.webhook.download_logs')).toBeInTheDocument();
    });

    it('includes the connection code in the href when an enabled subscription is provided', () => {
        const {container} = renderWithProviders(<DownloadLogsButton eventSubscription={enabledSubscription} />);

        const link = container.querySelector('a');
        expect(link?.href).toContain('magento');
    });

    it('has no href when no event subscription is provided', () => {
        const {container} = renderWithProviders(<DownloadLogsButton />);

        const link = container.querySelector('a');
        expect(link).toBeNull();
    });
});
