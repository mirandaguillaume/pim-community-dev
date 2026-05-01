import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {PageContent} from '@src/common/components/PageContent';
import {renderWithProviders} from '../../../test-utils';

describe('PageContent', () => {
    it('renders children inside the main content wrapper', () => {
        renderWithProviders(
            <PageContent>
                <p>Hello content</p>
            </PageContent>
        );

        expect(screen.getByText('Hello content')).toBeInTheDocument();
    });

    it('applies default min-height when no pageHeaderHeight is provided', () => {
        const {container} = renderWithProviders(<PageContent />);
        const mainContent = container.querySelector('.AknDefault-mainContent');

        expect(mainContent).toHaveStyle('min-height: calc(100vh - 126px)');
    });

    it('applies custom min-height when pageHeaderHeight is provided', () => {
        const {container} = renderWithProviders(<PageContent pageHeaderHeight={200} />);
        const mainContent = container.querySelector('.AknDefault-mainContent');

        expect(mainContent).toHaveStyle('min-height: calc(100vh - 200px)');
    });
});
