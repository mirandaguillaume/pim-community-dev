import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Link} from '@src/common/components/Typography';
import {renderWithProviders} from '../../../../test-utils';

describe('Link', () => {
    it('renders as an anchor element', () => {
        renderWithProviders(<Link href='https://example.com'>Visit</Link>);

        expect(screen.getByRole('link', {name: 'Visit'})).toBeInTheDocument();
    });

    it('has the correct href', () => {
        renderWithProviders(<Link href='https://example.com'>Visit</Link>);

        expect(screen.getByRole('link')).toHaveAttribute('href', 'https://example.com');
    });

    it('renders children', () => {
        renderWithProviders(<Link>Click here</Link>);

        expect(screen.getByText('Click here')).toBeInTheDocument();
    });
});
