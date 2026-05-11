import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {buildImageFieldAttribute} from './buildImageFieldAttribute';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useUploader: jest.fn(() => [jest.fn(), false]),
}));

jest.mock('akeneo-design-system', () => ({
  ...jest.requireActual('akeneo-design-system'),
  MediaFileInput: ({placeholder, children}: any) => (
    <div data-testid="media-file-input">{placeholder}{children}</div>
  ),
  useInModal: jest.fn(() => false),
}));

jest.mock('../file/preview/fullscreen-preview', () => ({
  FullscreenPreview: () => <div data-testid="fullscreen-preview" />,
}));

const attribute = {
  uuid: 'attr-uuid',
  code: 'photo',
  type: 'image' as const,
  order: 0,
  is_scopable: false,
  is_localizable: false,
  labels: {en_US: 'Photo'},
  template_uuid: 'tmpl-uuid',
};

const defaultProps = {
  channel: {code: 'ecommerce', label: 'Ecommerce'},
  locale: 'en_US',
  value: null,
  onChange: jest.fn(),
};

describe('buildImageFieldAttribute', () => {
  it('returns a React component', () => {
    const Component = buildImageFieldAttribute(attribute);
    expect(typeof Component).toBe('function');
  });

  it('renders MediaFileInput with placeholder translation key', () => {
    const Component = buildImageFieldAttribute(attribute);
    renderWithProviders(<Component {...defaultProps} />);
    expect(screen.getByTestId('media-file-input')).toBeInTheDocument();
    expect(screen.getByText('pim_common.media_upload')).toBeInTheDocument();
  });

  it('renders the attribute label', () => {
    const Component = buildImageFieldAttribute(attribute);
    renderWithProviders(<Component {...defaultProps} />);
    expect(screen.getByText('Photo')).toBeInTheDocument();
  });

  it('renders the fullscreen button when not in a modal', () => {
    const Component = buildImageFieldAttribute(attribute);
    renderWithProviders(<Component {...defaultProps} />);
    expect(screen.getByTitle('Fullscreen')).toBeInTheDocument();
  });

  it('does not render the fullscreen button when in a modal', () => {
    const {useInModal} = require('akeneo-design-system');
    useInModal.mockReturnValueOnce(true);
    const Component = buildImageFieldAttribute(attribute);
    renderWithProviders(<Component {...defaultProps} />);
    expect(screen.queryByTitle('Fullscreen')).not.toBeInTheDocument();
  });
});
