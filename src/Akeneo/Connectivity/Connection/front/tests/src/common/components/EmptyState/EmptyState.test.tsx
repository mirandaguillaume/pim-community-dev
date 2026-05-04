import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {EmptyState, Heading, Caption, Illustration} from '@src/common/components/EmptyState';
import {renderWithProviders} from '../../../../test-utils';

describe('EmptyState', () => {
    it('renders children', () => {
        renderWithProviders(<EmptyState>Content</EmptyState>);

        expect(screen.getByText('Content')).toBeInTheDocument();
    });
});

describe('Heading', () => {
    it('renders children', () => {
        renderWithProviders(<Heading>No results found</Heading>);

        expect(screen.getByText('No results found')).toBeInTheDocument();
    });
});

describe('Caption', () => {
    it('renders children', () => {
        renderWithProviders(<Caption>Try adjusting your search.</Caption>);

        expect(screen.getByText('Try adjusting your search.')).toBeInTheDocument();
    });
});

describe('Illustration', () => {
    it('renders an img element', () => {
        const {container} = renderWithProviders(<Illustration />);

        expect(container.querySelector('img')).toBeInTheDocument();
    });

    it('renders with the given width', () => {
        const {container} = renderWithProviders(<Illustration width={80} />);

        expect(container.querySelector('img')).toHaveAttribute('width', '80');
    });
});
