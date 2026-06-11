// Mock legacy/Backbone deps BEFORE importing the cell (jest.mock is hoisted).
jest.mock('@akeneo-pim-community/legacy-bridge', () => ({
  DependenciesProvider: (props: any) => props.children,
}));

jest.mock(
  'oro/datagrid/string-cell',
  () => {
    class MockStringCell {
      el: HTMLElement;
      constructor() {
        this.el = document.createElement('td');
      }
      remove() {
        return this;
      }
    }
    return MockStringCell;
  },
  {virtual: true}
);

jest.mock('oro/translator', () => jest.fn((key: string) => key), {virtual: true});

import React from 'react';
import {CompletenessBadge} from '../../../Resources/public/js/datagrid/cell/CompletenessBadge';

const CompleteVariantProductCell = require('../../../Resources/public/js/datagrid/cell/complete-variant-product-cell');

const makeCell = (documentType: string, data: any) => {
  const cell = new CompleteVariantProductCell();
  cell.model = {get: (key: string) => (key === 'document_type' ? documentType : undefined)};
  cell.formatter = {fromRaw: () => data};
  cell.column = {get: () => 'complete_variant_product'};

  return cell;
};

describe('complete-variant-product-cell reactContent', () => {
  test('renders the not-available label for a non product model', () => {
    const content = makeCell('product', {complete: 5, total: 5}).reactContent();
    expect(content.type).toBe(React.Fragment);
    expect(content.props.children).toBe('pim_common.not_available');
  });

  test('renders a success badge when all variants are complete', () => {
    const content = makeCell('product_model', {complete: 5, total: 5}).reactContent();
    expect(content.type).toBe(CompletenessBadge);
    expect(content.props.level).toBe('success');
    expect(content.props.label).toBe('5 / 5');
  });

  test('renders an important badge when none are complete', () => {
    const content = makeCell('product_model', {complete: 0, total: 5}).reactContent();
    expect(content.props.level).toBe('important');
    expect(content.props.label).toBe('0 / 5');
  });

  test('renders an important badge when the total is zero', () => {
    const content = makeCell('product_model', {complete: 0, total: 0}).reactContent();
    expect(content.props.level).toBe('important');
    expect(content.props.label).toBe('0 / 0');
  });

  test('renders a warning badge when partially complete', () => {
    const content = makeCell('product_model', {complete: 3, total: 5}).reactContent();
    expect(content.props.level).toBe('warning');
    expect(content.props.label).toBe('3 / 5');
  });

  test('renders a dash when the data is null', () => {
    const content = makeCell('product_model', null).reactContent();
    expect(content.type).toBe(React.Fragment);
    expect(content.props.children).toBe('-');
  });
});
