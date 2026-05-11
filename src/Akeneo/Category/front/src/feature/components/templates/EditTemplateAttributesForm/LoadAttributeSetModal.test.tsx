import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {LoadAttributeSetModal} from './LoadAttributeSetModal';

jest.mock('../../../tools/apiFetch', () => ({
  apiFetch: jest.fn().mockResolvedValue(undefined),
}));

const renderModal = (onClose = jest.fn(), onSuccess = jest.fn()) => {
  const queryClient = new QueryClient({defaultOptions: {mutations: {retry: false}}});
  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <LoadAttributeSetModal templateId="tmpl-uuid" onClose={onClose} onSuccess={onSuccess} />
    </QueryClientProvider>
  );
};

describe('LoadAttributeSetModal', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the section title', () => {
    renderModal();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.section_title')
    ).toBeInTheDocument();
  });

  it('renders the modal title', () => {
    renderModal();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.title')
    ).toBeInTheDocument();
  });

  it('renders the four attribute list items', () => {
    renderModal();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.content.description_attributes')
    ).toBeInTheDocument();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.content.url_attributes')
    ).toBeInTheDocument();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.content.image_attributes')
    ).toBeInTheDocument();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.content.seo_attributes')
    ).toBeInTheDocument();
  });

  it('calls onClose when the cancel button is clicked', async () => {
    const onClose = jest.fn();
    renderModal(onClose);
    await userEvent.click(screen.getByText('pim_common.cancel'));
    expect(onClose).toHaveBeenCalledTimes(1);
  });

  it('renders the load button', () => {
    renderModal();
    expect(
      screen.getByText('akeneo.category.template.load_attribute_set.button.load')
    ).toBeInTheDocument();
  });
});
