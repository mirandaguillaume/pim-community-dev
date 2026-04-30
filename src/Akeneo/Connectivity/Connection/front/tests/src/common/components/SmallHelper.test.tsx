import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {SmallHelper} from '@src/common/components/SmallHelper';
import {renderWithProviders} from '../../test-utils';

describe('SmallHelper', () => {
    it('renders children', () => {
        renderWithProviders(<SmallHelper>Helper text</SmallHelper>);

        expect(screen.getByText('Helper text')).toBeInTheDocument();
    });

    it('renders in info level by default', () => {
        const {container} = renderWithProviders(<SmallHelper>Info message</SmallHelper>);

        expect(container.firstChild).toBeInTheDocument();
    });

    it('renders in warning level when warning prop is set', () => {
        const {container} = renderWithProviders(<SmallHelper warning>Warning message</SmallHelper>);

        expect(container.firstChild).toBeInTheDocument();
        expect(screen.getByText('Warning message')).toBeInTheDocument();
    });
});
