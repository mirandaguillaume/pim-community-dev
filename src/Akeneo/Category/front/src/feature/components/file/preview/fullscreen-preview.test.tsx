import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {FullscreenPreview} from './fullscreen-preview';

jest.mock('./media-preview', () => ({
  MediaPreview: () => <div data-testid="media-preview" />,
}));

const attribute = {
  uuid: 'attr-uuid',
  code: 'photo',
  type: 'image' as const,
  order: 0,
  is_scopable: false,
  is_localizable: false,
  labels: {},
  template_uuid: 'tmpl-uuid',
};

const fileData = {
  filePath: 'a/b/photo.jpg',
  originalFilename: 'photo.jpg',
  size: 1024,
  mimeType: 'image/jpeg',
  extension: 'jpg',
};

describe('FullscreenPreview', () => {
  it('renders the label as the modal title', () => {
    renderWithProviders(
      <FullscreenPreview label="My Photo" attribute={attribute} data={fileData} onClose={jest.fn()} />
    );
    expect(screen.getByText('My Photo')).toBeInTheDocument();
  });

  it('renders the MediaPreview component', () => {
    renderWithProviders(
      <FullscreenPreview label="My Photo" attribute={attribute} data={fileData} onClose={jest.fn()} />
    );
    expect(screen.getByTestId('media-preview')).toBeInTheDocument();
  });

  it('renders the download button when data has a filePath and originalFilename', () => {
    renderWithProviders(
      <FullscreenPreview label="My Photo" attribute={attribute} data={fileData} onClose={jest.fn()} />
    );
    expect(screen.getByText('Download')).toBeInTheDocument();
  });

  it('does not render the download button when data is null', () => {
    renderWithProviders(
      <FullscreenPreview label="My Photo" attribute={attribute} data={null} onClose={jest.fn()} />
    );
    expect(screen.queryByText('Download')).not.toBeInTheDocument();
  });

  it('calls onClose when the close button is clicked', async () => {
    const onClose = jest.fn();
    renderWithProviders(
      <FullscreenPreview label="My Photo" attribute={attribute} data={fileData} onClose={onClose} />
    );
    await userEvent.click(screen.getByTitle('pim_common.close'));
    expect(onClose).toHaveBeenCalledTimes(1);
  });
});
