import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NoEventLogs} from '@src/webhook/components/NoEventLogs';
import {renderWithProviders} from '../../../test-utils';

describe('NoEventLogs', () => {
    it('renders the no event logs heading', () => {
        renderWithProviders(<NoEventLogs />);

        expect(
            screen.getByText('akeneo_connectivity.connection.webhook.event_logs.no_event_logs.title')
        ).toBeInTheDocument();
    });
});
