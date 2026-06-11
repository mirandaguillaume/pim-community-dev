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

// Identity translator: the asserted not-available text equals the translation key.
jest.mock('oro/translator', () => jest.fn((key: string) => key), {virtual: true});

import React from 'react';
import {CompletenessBadge} from '../../../Resources/public/js/datagrid/cell/CompletenessBadge';

const CompletenessCell = require('../../../Resources/public/js/datagrid/cell/completeness-cell');

// formatter.fromRaw is stubbed to return `ratio` directly, decoupling the test
// from the model field name.
const makeCell = (documentType: string, ratio: any) => {
  const cell = new CompletenessCell();
  cell.model = {get: (key: string) => (key === 'document_type' ? documentType : undefined)};
  cell.formatter = {fromRaw: () => ratio};
  cell.column = {get: () => 'completeness'};

  return cell;
};

describe('completeness-cell reactContent', () => {
  test('renders the not-available label for a product model', () => {
    const content = makeCell('product_model', 100).reactContent();
    expect(content.type).toBe(React.Fragment);
    expect(content.props.children).toBe('pim_common.not_available');
  });

  test('renders a success badge at 100%', () => {
    const content = makeCell('product', 100).reactContent();
    expect(content.type).toBe(CompletenessBadge);
    expect(content.props.level).toBe('success');
    expect(content.props.label).toBe('100%');
  });

  test('renders an important badge at 0%', () => {
    const content = makeCell('product', 0).reactContent();
    expect(content.props.level).toBe('important');
    expect(content.props.label).toBe('0%');
  });

  test('renders a warning badge for an in-between ratio', () => {
    const content = makeCell('product', 42).reactContent();
    expect(content.props.level).toBe('warning');
    expect(content.props.label).toBe('42%');
  });

  test('renders a dash when the ratio is null', () => {
    const content = makeCell('product', null).reactContent();
    expect(content.type).toBe(React.Fragment);
    expect(content.props.children).toBe('-');
  });
});
