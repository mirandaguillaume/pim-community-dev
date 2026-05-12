import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MediaPreview} from './media-preview';

jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  Image: ({alt, src}: {alt: string; src: string}) => <img alt={alt} src={src} />,
}));

const renderPreview = (previewUrl: string, label: string) =>
  render(
    <ThemeProvider theme={pimTheme}>
      <MediaPreview previewUrl={previewUrl} label={label} />
    </ThemeProvider>
  );

describe('MediaPreview', () => {
  it('renders an image with the given alt text', () => {
    renderPreview('https://cdn/photo.jpg', 'My Photo');
    expect(screen.getByAltText('My Photo')).toBeInTheDocument();
  });

  it('renders an image with the given src URL', () => {
    renderPreview('https://cdn/photo.jpg', 'My Photo');
    expect(screen.getByRole('img')).toHaveAttribute('src', 'https://cdn/photo.jpg');
  });
});
