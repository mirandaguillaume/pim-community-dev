import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {NoEventLogsWithThoseFilters} from '@src/webhook/components/NoEventLogsWithThoseFilters';
import {renderWithProviders} from '../../../test-utils';

describe('NoEventLogsWithThoseFilters', () => {
    it('renders the no results heading and caption', () => {
        renderWithProviders(<NoEventLogsWithThoseFilters />);

        expect(
            screen.getByText('akeneo_connectivity.connection.webhook.event_logs.no_event_logs_with_those_filters.title')
        ).toBeInTheDocument();
        expect(
            screen.getByText(
                'akeneo_connectivity.connection.webhook.event_logs.no_event_logs_with_those_filters.caption'
            )
        ).toBeInTheDocument();
    });
});
