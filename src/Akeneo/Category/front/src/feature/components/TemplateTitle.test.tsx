import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {TemplateTitle} from './TemplateTitle';
import {Template} from '../models';

const template: Template = {
  uuid: 'tmpl-uuid',
  code: 'tshirts',
  labels: {en_US: 'T-Shirts', fr_FR: 'Tee-Shirts'},
  category_tree_identifier: 1,
  attributes: [],
};

describe('TemplateTitle', () => {
  it('displays the label for the given locale', () => {
    renderWithProviders(<TemplateTitle template={template} locale="fr_FR" />);
    expect(screen.getByText('Tee-Shirts')).toBeInTheDocument();
  });

  it('falls back to [code] when the locale has no label', () => {
    renderWithProviders(<TemplateTitle template={template} locale="de_DE" />);
    expect(screen.getByText('[tshirts]')).toBeInTheDocument();
  });

  it('uses the catalog locale (en_US from mockedDependencies) when locale prop is null', () => {
    renderWithProviders(<TemplateTitle template={template} locale={null} />);
    expect(screen.getByText('T-Shirts')).toBeInTheDocument();
  });

  it('renders the section title translation key', () => {
    renderWithProviders(<TemplateTitle template={template} locale="en_US" />);
    expect(screen.getByText(/akeneo\.category\.edition_form\.template\.title/)).toBeInTheDocument();
  });
});
