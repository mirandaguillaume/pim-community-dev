import React from 'react';
import '@testing-library/jest-dom';
import {screen} from '@testing-library/react';
import {Figure, FigureImage, FigureCaption} from '@src/common/components/Figure';
import {renderWithProviders} from '../../../test-utils';

describe('Figure', () => {
    it('renders a div element', () => {
        const {container} = renderWithProviders(<Figure />);

        expect(container.querySelector('div')).toBeInTheDocument();
    });

    it('renders children', () => {
        renderWithProviders(<Figure>Image content</Figure>);

        expect(screen.getByText('Image content')).toBeInTheDocument();
    });
});

describe('FigureImage', () => {
    it('renders an img element', () => {
        const {container} = renderWithProviders(<FigureImage src='photo.jpg' alt='Photo' />);

        expect(container.querySelector('img')).toBeInTheDocument();
    });

    it('passes src and alt to the image', () => {
        const {container} = renderWithProviders(<FigureImage src='photo.jpg' alt='Photo' />);

        const img = container.querySelector('img');
        expect(img).toHaveAttribute('src', 'photo.jpg');
        expect(img).toHaveAttribute('alt', 'Photo');
    });
});

describe('FigureCaption', () => {
    it('renders children', () => {
        renderWithProviders(<FigureCaption>My caption</FigureCaption>);

        expect(screen.getByText('My caption')).toBeInTheDocument();
    });
});
