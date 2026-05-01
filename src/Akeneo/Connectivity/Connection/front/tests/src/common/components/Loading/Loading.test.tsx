import React from 'react';
import '@testing-library/jest-dom';
import {Loading} from '@src/common/components/Loading/Loading';
import {renderWithProviders} from '../../../../test-utils';

describe('Loading', () => {
    it('renders the loading spinner svg', () => {
        const {container} = renderWithProviders(<Loading />);

        expect(container.querySelector('svg')).toBeInTheDocument();
    });
});
