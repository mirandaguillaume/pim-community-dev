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

// oro/translator mocked as identity, so the asserted label equals the translation key.
jest.mock('oro/translator', () => jest.fn((key: string) => key), {virtual: true});

import {EnabledBadge} from '../../../Resources/public/js/datagrid/cell/EnabledBadge';

const EnabledCell = require('../../../Resources/public/js/datagrid/cell/enabled-cell');

const makeCell = (model: Record<string, any>) => {
  const cell = new EnabledCell();
  cell.model = {get: (key: string) => model[key]};
  cell.formatter = {fromRaw: (value: any) => value};
  cell.column = {get: () => 'enabled'};

  return cell;
};

describe('enabled-cell reactContent', () => {
  test('renders an enabled badge for an enabled product', () => {
    const content = makeCell({document_type: 'product', enabled: true}).reactContent();

    expect(content.type).toBe(EnabledBadge);
    expect(content.props.enabled).toBe(true);
    expect(content.props.label).toBe('pim_enrich.entity.product.module.status.enabled');
  });

  test('renders a disabled badge for a disabled product', () => {
    const content = makeCell({document_type: 'product', enabled: false}).reactContent();

    expect(content.type).toBe(EnabledBadge);
    expect(content.props.enabled).toBe(false);
    expect(content.props.label).toBe('pim_enrich.entity.product.module.status.disabled');
  });

  test('renders nothing for a product model (status is computed from the subtree)', () => {
    const content = makeCell({document_type: 'product_model', enabled: true}).reactContent();

    expect(content).toBeNull();
  });
});
