import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {NoTemplateAttribute} from './NoTemplateAttribute';

describe('NoTemplateAttribute', () => {
  it('renders the title translation key', () => {
    renderWithProviders(<NoTemplateAttribute />);
    expect(
      screen.getByText('akeneo.category.edition_form.template.no_attribute_title')
    ).toBeInTheDocument();
  });

  it('renders the instructions translation key', () => {
    renderWithProviders(<NoTemplateAttribute />);
    expect(
      screen.getByText('akeneo.category.edition_form.template.no_attribute_instructions')
    ).toBeInTheDocument();
  });

  it('renders the learn more link translation key', () => {
    renderWithProviders(<NoTemplateAttribute />);
    expect(
      screen.getByText('akeneo.category.template.learn_more')
    ).toBeInTheDocument();
  });
});
