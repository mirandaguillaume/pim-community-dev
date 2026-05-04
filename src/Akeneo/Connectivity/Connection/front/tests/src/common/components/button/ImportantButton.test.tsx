import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {ImportantButton} from '@src/common/components/button/ImportantButton';
import {renderWithProviders} from '../../../../test-utils';

describe('ImportantButton', () => {
    it('renders children', () => {
        renderWithProviders(<ImportantButton className=''>Delete</ImportantButton>);

        expect(screen.getByRole('button', {name: 'Delete'})).toBeInTheDocument();
    });

    it('has the AknButton--important class', () => {
        renderWithProviders(<ImportantButton className=''>Delete</ImportantButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton--important');
    });

    it('has the AknButton class from the base Button', () => {
        renderWithProviders(<ImportantButton className=''>Delete</ImportantButton>);

        expect(screen.getByRole('button')).toHaveClass('AknButton');
    });
});
