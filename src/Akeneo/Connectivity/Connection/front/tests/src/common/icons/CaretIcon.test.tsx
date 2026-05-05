import React from 'react';
import '@testing-library/jest-dom';
import {CaretDownIcon, CaretUpIcon} from '@src/common/icons/CaretIcon';
import {renderWithProviders} from '../../../test-utils';

describe('CaretIcon', () => {
    it('renders CaretDownIcon as a span', () => {
        const {container} = renderWithProviders(<CaretDownIcon />);

        expect(container.querySelector('span')).toBeInTheDocument();
    });

    it('renders CaretUpIcon as a span', () => {
        const {container} = renderWithProviders(<CaretUpIcon />);

        expect(container.querySelector('span')).toBeInTheDocument();
    });

    it('renders CaretDownIcon and CaretUpIcon as separate elements', () => {
        const {container} = renderWithProviders(
            <>
                <CaretDownIcon />
                <CaretUpIcon />
            </>
        );

        expect(container.querySelectorAll('span').length).toBe(2);
    });
});
