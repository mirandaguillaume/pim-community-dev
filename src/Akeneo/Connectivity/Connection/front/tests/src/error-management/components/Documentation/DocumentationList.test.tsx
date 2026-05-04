import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {DocumentationList} from '@src/error-management/components/Documentation/DocumentationList';
import {DocumentationStyleText, DocumentationStyleInformation} from '@src/error-management/model/ConnectionError';
import {renderWithProviders} from '../../../../test-utils';

describe('DocumentationList', () => {
    it('renders text-style documentation messages', () => {
        renderWithProviders(
            <DocumentationList
                documentations={[{message: 'Check this.', parameters: {}, style: DocumentationStyleText}]}
            />
        );

        expect(screen.getByText('Check this.')).toBeInTheDocument();
    });

    it('renders information-style documentation messages', () => {
        renderWithProviders(
            <DocumentationList
                documentations={[{message: 'More info here.', parameters: {}, style: DocumentationStyleInformation}]}
            />
        );

        expect(screen.getByText('More info here.')).toBeInTheDocument();
    });

    it('renders both text and information documentations', () => {
        renderWithProviders(
            <DocumentationList
                documentations={[
                    {message: 'Text message.', parameters: {}, style: DocumentationStyleText},
                    {message: 'Info message.', parameters: {}, style: DocumentationStyleInformation},
                ]}
            />
        );

        expect(screen.getByText('Text message.')).toBeInTheDocument();
        expect(screen.getByText('Info message.')).toBeInTheDocument();
    });

    it('renders nothing for an empty list', () => {
        const {container} = renderWithProviders(<DocumentationList documentations={[]} />);

        expect(container.textContent).toBe('');
    });
});
