import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {EventSubscriptionHelper} from '@src/webhook/components/EventSubscriptionHelper';
import {renderWithProviders} from '../../../test-utils';

describe('EventSubscriptionHelper', () => {
    it('renders the helper message', () => {
        const {container} = renderWithProviders(<EventSubscriptionHelper />);

        expect(container.textContent).toContain('akeneo_connectivity.connection.webhook.helper.message');
    });

    it('renders the documentation link with the correct href and target', () => {
        renderWithProviders(<EventSubscriptionHelper />);

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute(
            'href',
            'https://help.akeneo.com/pim/serenity/articles/manage-event-subscription.html'
        );
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'noopener noreferrer');
    });
});
