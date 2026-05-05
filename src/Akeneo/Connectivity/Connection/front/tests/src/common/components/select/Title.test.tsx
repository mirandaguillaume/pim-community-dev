import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Title} from '@src/common/components/select/Title';
import {renderWithProviders} from '../../../../test-utils';

describe('Title', () => {
    it('renders children', () => {
        renderWithProviders(<Title>Section Label</Title>);

        expect(screen.getByText('Section Label')).toBeInTheDocument();
    });

    it('renders as a div', () => {
        const {container} = renderWithProviders(<Title>content</Title>);

        expect(container.querySelector('div')).toBeInTheDocument();
    });
});
