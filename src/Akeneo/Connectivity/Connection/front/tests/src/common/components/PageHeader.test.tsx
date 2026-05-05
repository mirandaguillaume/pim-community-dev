import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {PageHeader} from '@src/common/components/PageHeader';
import {renderWithProviders} from '../../../test-utils';

describe('PageHeader', () => {
    it('renders the title (children)', () => {
        renderWithProviders(<PageHeader>My Page Title</PageHeader>);

        expect(screen.getByText('My Page Title')).toBeInTheDocument();
    });

    it('renders the breadcrumb when provided', () => {
        renderWithProviders(<PageHeader breadcrumb={<span>Home / Page</span>}>Title</PageHeader>);

        expect(screen.getByText('Home / Page')).toBeInTheDocument();
    });

    it('renders buttons when provided', () => {
        renderWithProviders(<PageHeader buttons={[<button key='save'>Save</button>]}>Title</PageHeader>);

        expect(screen.getByRole('button', {name: 'Save'})).toBeInTheDocument();
    });

    it('renders an img when imageSrc is provided', () => {
        const {container} = renderWithProviders(<PageHeader imageSrc='logo.png'>Title</PageHeader>);

        expect(container.querySelector('img')).toBeInTheDocument();
    });

    it('renders the state node when provided', () => {
        renderWithProviders(<PageHeader state={<span>Enabled</span>}>Title</PageHeader>);

        expect(screen.getByText('Enabled')).toBeInTheDocument();
    });
});
