import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Flag} from '@src/common/components/Flag';
import {LocaleContext} from '@src/shared/locale/locale-context';
import {renderWithProviders} from '../../../test-utils';

describe('Flag', () => {
    it('renders the raw locale code when no LocaleContext is provided', () => {
        renderWithProviders(<Flag locale='en_US' />);

        expect(screen.getByText('en_US')).toBeInTheDocument();
    });

    it('renders the language label when locale is found in context', () => {
        renderWithProviders(
            <LocaleContext.Provider value={[{code: 'en_US', language: 'English'}]}>
                <Flag locale='en_US' />
            </LocaleContext.Provider>
        );

        expect(screen.getByText('English')).toBeInTheDocument();
    });

    it('renders a flag icon with the region code when locale is found', () => {
        const {container} = renderWithProviders(
            <LocaleContext.Provider value={[{code: 'en_US', language: 'English'}]}>
                <Flag locale='en_US' />
            </LocaleContext.Provider>
        );

        expect(container.querySelector('.flag-us')).toBeInTheDocument();
    });
});
