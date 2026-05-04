import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ApplyButton} from '@src/common/components/button/ApplyButton';
import {renderWithProviders} from '../../../../test-utils';

describe('ApplyButton', () => {
    it('renders children', () => {
        renderWithProviders(<ApplyButton className=''>Apply</ApplyButton>);

        expect(screen.getByRole('button', {name: 'Apply'})).toBeInTheDocument();
    });

    it('has the AknButton--apply class', () => {
        renderWithProviders(<ApplyButton className=''>Apply</ApplyButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton--apply');
    });

    it('has the AknButton class from the base Button', () => {
        renderWithProviders(<ApplyButton className=''>Apply</ApplyButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton');
    });
});
