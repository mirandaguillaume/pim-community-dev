import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {InitializeTemplateChoice} from './InitializeTemplateChoice';

jest.mock('../../../hooks/useTrackUsageOfLoadPredefinedAttributes', () => ({
  useTrackUsageOfLoadPredefinedAttributes: jest.fn(() => jest.fn()),
}));

jest.mock('../AddTemplateAttributeModal', () => ({
  AddTemplateAttributeModal: () => <div data-testid="add-attr-modal" />,
}));

jest.mock('./LoadAttributeSetModal', () => ({
  LoadAttributeSetModal: () => <div data-testid="load-attr-set-modal" />,
}));

describe('InitializeTemplateChoice', () => {
  it('renders the initialize title', () => {
    renderWithProviders(<InitializeTemplateChoice templateId="tmpl-uuid" />);
    expect(
      screen.getByText('akeneo.category.template.initialize.title')
    ).toBeInTheDocument();
  });

  it('renders the load and create buttons', () => {
    renderWithProviders(<InitializeTemplateChoice templateId="tmpl-uuid" />);
    expect(screen.getByText('akeneo.category.template.initialize.button.load')).toBeInTheDocument();
    expect(screen.getByText('akeneo.category.template.initialize.button.create')).toBeInTheDocument();
  });

  it('shows LoadAttributeSetModal when the load button is clicked', async () => {
    renderWithProviders(<InitializeTemplateChoice templateId="tmpl-uuid" />);
    expect(screen.queryByTestId('load-attr-set-modal')).not.toBeInTheDocument();
    await userEvent.click(screen.getByText('akeneo.category.template.initialize.button.load'));
    expect(screen.getByTestId('load-attr-set-modal')).toBeInTheDocument();
  });

  it('shows AddTemplateAttributeModal when the create button is clicked', async () => {
    renderWithProviders(<InitializeTemplateChoice templateId="tmpl-uuid" />);
    expect(screen.queryByTestId('add-attr-modal')).not.toBeInTheDocument();
    await userEvent.click(screen.getByText('akeneo.category.template.initialize.button.create'));
    expect(screen.getByTestId('add-attr-modal')).toBeInTheDocument();
  });
});
