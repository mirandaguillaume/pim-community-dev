import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {DocumentationMessage} from '@src/error-management/components/Documentation/DocumentationMessage';
import {
    Documentation,
    DocumentationStyleText,
    DocumentationStyleInformation,
    HrefType,
} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

describe('DocumentationMessage', () => {
    it('renders plain text parts of the message', () => {
        const doc: Documentation = {
            message: 'Please check the documentation.',
            parameters: {},
            style: DocumentationStyleText,
        };

        renderWithProviders(<DocumentationMessage documentation={doc} />);

        expect(screen.getByText('Please check the documentation.')).toBeInTheDocument();
    });

    it('replaces {param} with an href link', () => {
        const doc: Documentation = {
            message: 'Read {link} for details.',
            parameters: {
                link: {type: HrefType, href: 'https://example.com', title: 'the docs'},
            },
            style: DocumentationStyleText,
        };

        renderWithProviders(<DocumentationMessage documentation={doc} />);

        const link = screen.getByRole('link', {name: 'the docs'});
        expect(link).toHaveAttribute('href', 'https://example.com');
    });

    it('renders an info icon for information style', () => {
        const doc: Documentation = {
            message: 'More info here.',
            parameters: {},
            style: DocumentationStyleInformation,
        };

        const {container} = renderWithProviders(<DocumentationMessage documentation={doc} />);

        expect(container.querySelector('svg')).toBeInTheDocument();
    });
});
