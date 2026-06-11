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

// MediaUrlGenerator (pim/media-url-generator) lives in public/bundles — virtual mock.
// A null filePath yields the placeholder url, mirroring the real generator.
jest.mock(
  'pim/media-url-generator',
  () => ({
    __esModule: true,
    default: {
      getMediaShowUrl: jest.fn((filePath: string | null, filter: string) =>
        null === filePath ? `/placeholder/${filter}` : `/media/${filePath}/${filter}`
      ),
    },
  }),
  {virtual: true}
);

import {ProductImage} from '../../../Resources/public/js/datagrid/cell/ProductImage';

const ImageCell = require('../../../Resources/public/js/datagrid/cell/product-and-product-model-image-cell');

const makeCell = (documentType: string, image: any) => {
  const cell = new ImageCell();
  cell.model = {get: (key: string) => (key === 'document_type' ? documentType : undefined)};
  cell.formatter = {fromRaw: () => image};
  cell.column = {get: () => 'image'};

  return cell;
};

describe('product-and-product-model-image-cell reactContent', () => {
  test('renders a non-stacked product image with thumbnail + placeholder urls', () => {
    const content = makeCell('product', {filePath: 'a/b.jpg', originalFilename: 'b.jpg'}).reactContent();

    expect(content.type).toBe(ProductImage);
    expect(content.props.src).toBe('/media/a/b.jpg/thumbnail_small');
    expect(content.props.fallbackSrc).toBe('/placeholder/thumbnail_small');
    expect(content.props.label).toBe('b.jpg');
    expect(content.props.stacked).toBe(false);
  });

  test('renders the stacked variant for product models', () => {
    const content = makeCell('product_model', {filePath: 'x.jpg', originalFilename: 'x.jpg'}).reactContent();

    expect(content.props.stacked).toBe(true);
  });
});
