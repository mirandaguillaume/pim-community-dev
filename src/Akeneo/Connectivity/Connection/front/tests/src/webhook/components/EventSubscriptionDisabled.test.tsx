import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {EventSubscriptionDisabled} from '@src/webhook/components/EventSubscriptionDisabled';
import {renderWithProviders} from '../../../test-utils';

describe('EventSubscriptionDisabled', () => {
    it('renders the disabled title', () => {
        renderWithProviders(<EventSubscriptionDisabled connectionCode='magento' />);

        expect(
            screen.getByText('akeneo_connectivity.connection.webhook.event_logs.event_subscription_disabled.title')
        ).toBeInTheDocument();
    });

    it('renders a link to the event subscription settings for the given connection', () => {
        renderWithProviders(<EventSubscriptionDisabled connectionCode='magento' />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute('href', '#/connect/connection-settings/magento/event-subscription');
    });
});
