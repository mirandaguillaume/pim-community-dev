import React from 'react';
import '@testing-library/jest-dom';
import FormattedJSON from '@src/common/components/FormattedJSON';
import {renderWithProviders} from '../../../test-utils';

describe('FormattedJSON', () => {
    it('renders a pre element', () => {
        const {container} = renderWithProviders(<FormattedJSON>{{}}</FormattedJSON>);

        expect(container.querySelector('pre')).toBeInTheDocument();
    });

    it('renders string values with the stringJsonFormatted class', () => {
        const {container} = renderWithProviders(<FormattedJSON>{{key: 'value'}}</FormattedJSON>);

        expect(container.querySelector('.stringJsonFormatted')).toBeInTheDocument();
    });

    it('renders object keys with the keyJsonFormatted class', () => {
        const {container} = renderWithProviders(<FormattedJSON>{{key: 'value'}}</FormattedJSON>);

        expect(container.querySelector('.keyJsonFormatted')).toBeInTheDocument();
    });

    it('renders null values with the nullJsonFormatted class', () => {
        const {container} = renderWithProviders(<FormattedJSON>{{key: null}}</FormattedJSON>);

        expect(container.querySelector('.nullJsonFormatted')).toBeInTheDocument();
    });

    it('renders number values with the numberJsonFormatted class', () => {
        const {container} = renderWithProviders(<FormattedJSON>{{count: 42}}</FormattedJSON>);

        expect(container.querySelector('.numberJsonFormatted')).toBeInTheDocument();
    });
});
