import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Helper, HelperTitle, HelperLink} from '@src/common/components/Helper';
import {renderWithProviders} from '../../../test-utils';

describe('Helper', () => {
    it('renders description children', () => {
        renderWithProviders(<Helper>Some description</Helper>);

        expect(screen.getByText('Some description')).toBeInTheDocument();
    });

    it('renders HelperTitle children separately from description', () => {
        renderWithProviders(
            <Helper>
                <HelperTitle>My Title</HelperTitle>
                Some description
            </Helper>
        );

        expect(screen.getByText('My Title')).toBeInTheDocument();
        expect(screen.getByText('Some description')).toBeInTheDocument();
    });
});

describe('HelperLink', () => {
    it('renders as an anchor with the provided href', () => {
        renderWithProviders(<HelperLink href='https://example.com'>Click here</HelperLink>);

        const link = screen.getByRole('link', {name: 'Click here'});
        expect(link).toHaveAttribute('href', 'https://example.com');
    });
});
