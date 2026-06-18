// Mock Backbone/legacy deps BEFORE any import (jest.mock is hoisted).

jest.mock('@akeneo-pim-community/legacy-bridge', () => ({
  DependenciesProvider: (props: any) => props.children,
}));

jest.mock(
  'oro/datagrid/string-cell',
  () => {
    class MockStringCell {
      el: HTMLElement;
      model: any;
      column: any;
      formatter: any;
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

const ProductAndProductModelLabelCell = require('../../../Resources/public/js/datagrid/cell/product-and-product-model-label-cell');

const makeCell = (columnName: string, rawValue: string, documentType = 'product') => {
  const cell = new ProductAndProductModelLabelCell();
  cell.formatter = {fromRaw: (v: string) => v};
  cell.column = {get: (key: string) => (key === 'name' ? columnName : undefined)};
  cell.model = {
    get: (key: string) => {
      if (key === 'document_type') return documentType;
      if (key === columnName) return rawValue;
      return undefined;
    },
  };
  return cell;
};

describe('ProductAndProductModelLabelCell', () => {
  test('renders the label text into the cell element', () => {
    const cell = makeCell('label', 'My product');
    cell.render();
    expect(cell.el.textContent).toBe('My product');
  });

  test('sets the title attribute on the <td> to the raw column value', () => {
    const cell = makeCell('label', 'Sneakers');
    cell.render();
    expect(cell.el.title).toBe('Sneakers');
  });

  test('className adds the alternate highlight class for product models', () => {
    const cell = makeCell('label', 'Model X', 'product_model');
    expect(cell.className()).toContain('AknGrid-bodyCell--highlightAlternative');
  });

  test('className does not add the alternate highlight class for products', () => {
    const cell = makeCell('label', 'Product Y', 'product');
    expect(cell.className()).not.toContain('AknGrid-bodyCell--highlightAlternative');
  });

  test('always includes the base highlight class', () => {
    const cell = makeCell('label', 'X');
    expect(cell.className()).toContain('AknGrid-bodyCell--highlight');
  });

  test('renders an empty string when the column value is null', () => {
    const cell = makeCell('label', null as any);
    cell.render();
    expect(cell.el.textContent).toBe('');
  });
});
