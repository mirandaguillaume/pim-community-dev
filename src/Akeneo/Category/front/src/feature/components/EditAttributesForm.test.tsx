import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditAttributesForm} from './EditAttributesForm';
import {EditCategoryContext} from './providers/EditCategoryProvider';
import {Template, Attribute} from '../models';

jest.mock('./attributes', () => ({
  ...jest.requireActual('./attributes'),
  attributeFieldFactory: jest.fn(attribute => {
    const Field = ({value}: {value: any}) => <div data-testid={`field-${attribute.code}`}>{String(value ?? '')}</div>;
    return Field;
  }),
}));

const makeAttribute = (code: string): Attribute => ({
  uuid: `${code}-uuid`,
  code,
  type: 'text',
  order: 0,
  is_scopable: false,
  is_localizable: false,
  labels: {en_US: code},
  template_uuid: 'tmpl-uuid',
});

const makeTemplate = (attributes: Attribute[] = []): Template => ({
  uuid: 'tmpl-uuid',
  code: 'tshirts',
  labels: {en_US: 'T-Shirts'},
  category_tree_identifier: 1,
  attributes,
});

const ecommerce = {
  code: 'ecommerce',
  labels: {en_US: 'Ecommerce'},
  locales: [{code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'}],
};

const renderForm = (template: Template = makeTemplate(), attributeValues = {}, onAttributeValueChange = jest.fn()) =>
  renderWithProviders(
    <EditCategoryContext.Provider
      value={{channels: {ecommerce}, channelsFetchFailed: false, locales: {}, localesFetchFailed: false}}
    >
      <EditAttributesForm
        attributeValues={attributeValues}
        template={template}
        onAttributeValueChange={onAttributeValueChange}
      />
    </EditCategoryContext.Provider>
  );

describe('EditAttributesForm', () => {
  it('renders the attributes section title', () => {
    renderForm();
    expect(screen.getByText('akeneo.category.attributes')).toBeInTheDocument();
  });

  it('renders an attribute field for each attribute in the template', () => {
    const template = makeTemplate([makeAttribute('name'), makeAttribute('description')]);
    renderForm(template);
    expect(screen.getByTestId('field-name')).toBeInTheDocument();
    expect(screen.getByTestId('field-description')).toBeInTheDocument();
  });

  it('renders an error helper when attributeFieldFactory returns null for unknown type', () => {
    const {attributeFieldFactory} = require('./attributes');
    attributeFieldFactory.mockReturnValueOnce(null);
    const attr = {...makeAttribute('unknown'), type: 'unknown' as any};
    renderForm(makeTemplate([attr]));
    expect(
      screen.getByText(/akeneo\.category\.edition_form\.template\.fetching_failed/)
    ).toBeInTheDocument();
  });
});
