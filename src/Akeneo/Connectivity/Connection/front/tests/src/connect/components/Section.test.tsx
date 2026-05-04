import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Section} from '@src/connect/components/Section';
import {renderWithProviders} from '../../../test-utils';

describe('Section', () => {
    it('renders the title and information', () => {
        renderWithProviders(
            <Section title='My Apps' information='3 apps' emptyMessage='No apps yet'>
                <div>App 1</div>
            </Section>
        );

        expect(screen.getByText('My Apps')).toBeInTheDocument();
        expect(screen.getByText('3 apps')).toBeInTheDocument();
    });

    it('shows the empty message when there are no children', () => {
        renderWithProviders(<Section title='My Apps' information='0 apps' emptyMessage='No apps yet' />);

        expect(screen.getByText('No apps yet')).toBeInTheDocument();
    });

    it('renders children in a grid when provided', () => {
        renderWithProviders(
            <Section title='My Apps' information='1 app' emptyMessage='No apps yet'>
                <div>App 1</div>
            </Section>
        );

        expect(screen.getByText('App 1')).toBeInTheDocument();
        expect(screen.queryByText('No apps yet')).not.toBeInTheDocument();
    });

    it('renders the warning message when provided', () => {
        renderWithProviders(
            <Section title='My Apps' information='0 apps' emptyMessage='No apps' warningMessage='Max limit reached'>
                <div>App 1</div>
            </Section>
        );

        expect(screen.getByText('Max limit reached')).toBeInTheDocument();
    });

    it('does not render warning when warningMessage is null', () => {
        renderWithProviders(
            <Section title='My Apps' information='0 apps' emptyMessage='No apps' warningMessage={null}>
                <div>App 1</div>
            </Section>
        );

        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });
});
