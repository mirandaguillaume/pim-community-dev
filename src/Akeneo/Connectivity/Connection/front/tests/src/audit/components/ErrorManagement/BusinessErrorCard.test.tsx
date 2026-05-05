import React from 'react';
import '@testing-library/jest-dom';
import {screen, fireEvent} from '@testing-library/react';
import {BusinessErrorCard} from '@src/audit/components/ErrorManagement/BusinessErrorCard';
import {renderWithProviders} from '../../../../test-utils';

describe('BusinessErrorCard', () => {
    it('renders the connection label', () => {
        renderWithProviders(
            <BusinessErrorCard code='erp' label='ERP Connection' image={null} errorCount={5} onClick={jest.fn()} />
        );

        expect(screen.getByText('ERP Connection')).toBeInTheDocument();
    });

    it('renders the error count', () => {
        renderWithProviders(
            <BusinessErrorCard code='erp' label='ERP' image={null} errorCount={42} onClick={jest.fn()} />
        );

        expect(screen.getByText('42')).toBeInTheDocument();
    });

    it('renders the business_errors translation key', () => {
        const {container} = renderWithProviders(
            <BusinessErrorCard code='erp' label='ERP' image={null} errorCount={3} onClick={jest.fn()} />
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.dashboard.error_management.widget.business_errors'
        );
    });

    it('renders the over_the_last_seven_days translation key', () => {
        const {container} = renderWithProviders(
            <BusinessErrorCard code='erp' label='ERP' image={null} errorCount={3} onClick={jest.fn()} />
        );

        expect(container.textContent).toContain(
            'akeneo_connectivity.connection.dashboard.error_management.widget.over_the_last_seven_days'
        );
    });

    it('renders an img with alt equal to the label', () => {
        renderWithProviders(
            <BusinessErrorCard code='erp' label='My Connection' image={null} errorCount={0} onClick={jest.fn()} />
        );

        expect(screen.getByAltText('My Connection')).toBeInTheDocument();
    });

    it('calls onClick when the card is clicked', () => {
        const onClick = jest.fn();
        const {container} = renderWithProviders(
            <BusinessErrorCard code='erp' label='ERP' image={null} errorCount={1} onClick={onClick} />
        );

        fireEvent.click(container.firstChild as Element);

        expect(onClick).toHaveBeenCalledTimes(1);
    });
});
