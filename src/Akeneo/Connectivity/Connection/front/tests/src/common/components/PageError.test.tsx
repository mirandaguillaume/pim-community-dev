import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {PageError} from '@src/common/components/PageError';
import {renderWithProviders} from '../../../test-utils';

describe('PageError', () => {
    it('renders the title', () => {
        renderWithProviders(<PageError title='Something went wrong' />);

        expect(screen.getByText('Something went wrong')).toBeInTheDocument();
    });

    it('renders children as the message', () => {
        renderWithProviders(<PageError title='Error'>Please try again</PageError>);

        expect(screen.getByText('Please try again')).toBeInTheDocument();
    });

    it('renders an image with the default src when no imgUrl is provided', () => {
        const {container} = renderWithProviders(<PageError title='Error' />);
        const img = container.querySelector('img');

        expect(img).toBeInTheDocument();
        expect(img).toHaveAttribute('src');
    });

    it('renders an image with a custom imgUrl when provided', () => {
        const {container} = renderWithProviders(<PageError title='Error' imgUrl='custom.svg' />);
        const img = container.querySelector('img');

        expect(img).toHaveAttribute('src', 'custom.svg');
    });
});
