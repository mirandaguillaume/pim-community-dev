import React from 'react';
import '@testing-library/jest-dom';
import {LoadingSpinner} from '@src/common/components/Loading/LoadingSpinner';
import {renderWithProviders} from '../../../../test-utils';

describe('LoadingSpinner', () => {
    it('renders an svg element', () => {
        const {container} = renderWithProviders(<LoadingSpinner />);

        expect(container.querySelector('svg')).toBeInTheDocument();
    });

    it('renders with the lds-dash-ring class', () => {
        const {container} = renderWithProviders(<LoadingSpinner />);

        expect(container.querySelector('svg')).toHaveClass('lds-dash-ring');
    });

    it('accepts extra svg props', () => {
        const {container} = renderWithProviders(<LoadingSpinner data-testid='spinner' />);

        expect(container.querySelector('svg')).toHaveAttribute('data-testid', 'spinner');
    });
});
