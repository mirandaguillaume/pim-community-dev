import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Loading} from '@src/settings/components/Loading';
import {renderWithProviders} from '../../../test-utils';

describe('Loading', () => {
    it('displays the upload ratio as a percentage', () => {
        renderWithProviders(<Loading ratio={42} />);

        expect(screen.getByText('42%')).toBeInTheDocument();
    });

    it('displays 0% when ratio is zero', () => {
        renderWithProviders(<Loading ratio={0} />);

        expect(screen.getByText('0%')).toBeInTheDocument();
    });

    it('displays 100% when upload is complete', () => {
        renderWithProviders(<Loading ratio={100} />);

        expect(screen.getByText('100%')).toBeInTheDocument();
    });
});
