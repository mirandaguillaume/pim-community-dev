import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {GreyButton} from '@src/common/components/button/GreyButton';
import {renderWithProviders} from '../../../../test-utils';

describe('GreyButton', () => {
    it('renders children', () => {
        renderWithProviders(<GreyButton className=''>Cancel</GreyButton>);

        expect(screen.getByRole('button', {name: 'Cancel'})).toBeInTheDocument();
    });

    it('has the AknButton--grey class', () => {
        renderWithProviders(<GreyButton className=''>Cancel</GreyButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton--grey');
    });

    it('has the AknButton class from the base Button', () => {
        renderWithProviders(<GreyButton className=''>Cancel</GreyButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton');
    });
});
