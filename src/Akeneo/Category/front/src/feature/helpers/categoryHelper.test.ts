import {
  permissionsAreEqual,
  categoriesAreEqual,
  getAttributeValue,
  populateCategory,
  convertCategoryImageAttributeValueDataToFileInfo,
  convertFileInfoToCategoryImageAttributeValueData,
} from './categoryHelper';
import {EnrichCategory, CategoryAttributes, CategoryImageAttributeValueData} from '../models';
import {CategoryPermissions} from '../models/CategoryPermission';
import {Attribute} from '../models/Attribute';

const makePermissions = (view: number[] = [], edit: number[] = [], own: number[] = []): CategoryPermissions => ({
  view: view.map(id => ({id, label: `group-${id}`})),
  edit: edit.map(id => ({id, label: `group-${id}`})),
  own: own.map(id => ({id, label: `group-${id}`})),
});

const makeCategory = (overrides: Partial<EnrichCategory> = {}): EnrichCategory => ({
  id: 1,
  isRoot: false,
  template_uuid: null,
  root: null,
  properties: {code: 'master', labels: {en_US: 'Master'}},
  attributes: {},
  permissions: makePermissions([1], [1], [1]),
  ...overrides,
});

const makeAttribute = (overrides: Partial<Attribute> = {}): Attribute => ({
  uuid: 'attr-uuid-1',
  code: 'title',
  type: 'text',
  order: 0,
  is_scopable: false,
  is_localizable: false,
  labels: {},
  template_uuid: 'tmpl-uuid',
  ...overrides,
});

describe('permissionsAreEqual', () => {
  it('returns true for identical empty permissions', () => {
    expect(permissionsAreEqual(makePermissions(), makePermissions())).toBe(true);
  });

  it('returns true when permissions are the same', () => {
    const p = makePermissions([1, 2], [2], [2]);
    expect(permissionsAreEqual(p, p)).toBe(true);
  });

  it('returns true when permissions have same ids in different order', () => {
    expect(permissionsAreEqual(makePermissions([2, 1], [3, 4], [5, 6]), makePermissions([1, 2], [4, 3], [6, 5]))).toBe(
      true
    );
  });

  it('returns false when view ids differ', () => {
    expect(permissionsAreEqual(makePermissions([1], [2], [2]), makePermissions([3], [2], [2]))).toBe(false);
  });

  it('returns false when edit ids differ', () => {
    expect(permissionsAreEqual(makePermissions([1], [2], [2]), makePermissions([1], [3], [2]))).toBe(false);
  });

  it('returns false when own ids differ', () => {
    expect(permissionsAreEqual(makePermissions([1], [2], [2]), makePermissions([1], [2], [3]))).toBe(false);
  });

  it('returns false when one side has more ids', () => {
    expect(permissionsAreEqual(makePermissions([1, 2], [], []), makePermissions([1], [], []))).toBe(false);
  });
});

describe('categoriesAreEqual', () => {
  it('returns true for identical categories', () => {
    const c = makeCategory();
    expect(categoriesAreEqual(c, c)).toBe(true);
  });

  it('returns false when ids differ', () => {
    expect(categoriesAreEqual(makeCategory({id: 1}), makeCategory({id: 2}))).toBe(false);
  });

  it('returns false when property code differs', () => {
    expect(
      categoriesAreEqual(
        makeCategory({properties: {code: 'master', labels: {}}}),
        makeCategory({properties: {code: 'summer', labels: {}}})
      )
    ).toBe(false);
  });

  it('returns false when labels differ', () => {
    expect(
      categoriesAreEqual(
        makeCategory({properties: {code: 'master', labels: {en_US: 'Master'}}}),
        makeCategory({properties: {code: 'master', labels: {en_US: 'Other'}}})
      )
    ).toBe(false);
  });

  it('returns false when permissions differ', () => {
    expect(
      categoriesAreEqual(
        makeCategory({permissions: makePermissions([1], [], [])}),
        makeCategory({permissions: makePermissions([2], [], [])})
      )
    ).toBe(false);
  });

  it('returns false when attributes differ', () => {
    const attributes: CategoryAttributes = {
      'title|attr-uuid-1': {data: 'Hello', channel: null, locale: null, attribute_code: 'title|attr-uuid-1'},
    };
    expect(categoriesAreEqual(makeCategory({attributes}), makeCategory({attributes: {}}))).toBe(false);
  });
});

describe('getAttributeValue', () => {
  const attribute = makeAttribute({code: 'title', uuid: 'attr-uuid-1', is_scopable: false, is_localizable: false});

  it('returns data when composite key matches', () => {
    const attributes: CategoryAttributes = {
      'title|attr-uuid-1': {data: 'My Title', channel: null, locale: null, attribute_code: 'title|attr-uuid-1'},
    };
    expect(getAttributeValue(attributes, attribute, null, null)).toBe('My Title');
  });

  it('returns undefined when attribute not in attributes', () => {
    expect(getAttributeValue({}, attribute, null, null)).toBeUndefined();
  });

  it('builds composite key with channel and locale for scopable+localizable attribute', () => {
    const scopableLocalizable = makeAttribute({
      code: 'desc',
      uuid: 'attr-uuid-2',
      is_scopable: true,
      is_localizable: true,
    });
    const attributes: CategoryAttributes = {
      'desc|attr-uuid-2|ecommerce|en_US': {
        data: 'Description EN',
        channel: 'ecommerce',
        locale: 'en_US',
        attribute_code: 'desc|attr-uuid-2',
      },
    };
    expect(getAttributeValue(attributes, scopableLocalizable, 'ecommerce', 'en_US')).toBe('Description EN');
  });

  it('returns undefined when channel/locale combination does not match', () => {
    const scopableLocalizable = makeAttribute({
      code: 'desc',
      uuid: 'attr-uuid-2',
      is_scopable: true,
      is_localizable: true,
    });
    const attributes: CategoryAttributes = {
      'desc|attr-uuid-2|ecommerce|en_US': {
        data: 'Description EN',
        channel: 'ecommerce',
        locale: 'en_US',
        attribute_code: 'desc|attr-uuid-2',
      },
    };
    expect(getAttributeValue(attributes, scopableLocalizable, 'print', 'fr_FR')).toBeUndefined();
  });
});

describe('populateCategory', () => {
  it('converts null permissions to empty arrays', () => {
    const category = makeCategory({permissions: null as any});
    const result = populateCategory(category, null, [], []);
    expect(result.permissions).toEqual({view: [], edit: [], own: []});
  });

  it('converts null labels to empty object', () => {
    const category = makeCategory({properties: {code: 'master', labels: null as any}});
    const result = populateCategory(category, null, [], []);
    expect(result.properties.labels).toEqual({});
  });

  it('does not mutate the original category', () => {
    const category = makeCategory({permissions: null as any});
    const original = JSON.parse(JSON.stringify(category));
    populateCategory(category, null, [], []);
    expect(category.permissions).toEqual(original.permissions);
  });

  it('keeps existing permissions unchanged when not null', () => {
    const category = makeCategory({permissions: makePermissions([1], [2], [3])});
    const result = populateCategory(category, null, [], []);
    expect(result.permissions).toEqual(makePermissions([1], [2], [3]));
  });

  it('populates text attribute default value from template', () => {
    const attribute = makeAttribute({
      code: 'title',
      uuid: 'uuid-t',
      type: 'text',
      is_scopable: false,
      is_localizable: false,
    });
    const template = {uuid: 'tmpl', code: 'tpl', labels: {}, category_tree_identifier: 1, attributes: [attribute]};
    const category = makeCategory({attributes: {}});
    const result = populateCategory(category, template, [], []);
    expect(result.attributes['title|uuid-t']).toEqual({
      data: '',
      channel: null,
      locale: null,
      attribute_code: 'title|uuid-t',
    });
  });

  it('populates image attribute with null default', () => {
    const attribute = makeAttribute({
      code: 'img',
      uuid: 'uuid-i',
      type: 'image',
      is_scopable: false,
      is_localizable: false,
    });
    const template = {uuid: 'tmpl', code: 'tpl', labels: {}, category_tree_identifier: 1, attributes: [attribute]};
    const result = populateCategory(makeCategory({attributes: {}}), template, [], []);
    expect(result.attributes['img|uuid-i'].data).toBeNull();
  });

  it('does not overwrite existing attribute value from template', () => {
    const attribute = makeAttribute({
      code: 'title',
      uuid: 'uuid-t',
      type: 'text',
      is_scopable: false,
      is_localizable: false,
    });
    const template = {uuid: 'tmpl', code: 'tpl', labels: {}, category_tree_identifier: 1, attributes: [attribute]};
    const existing: CategoryAttributes = {
      'title|uuid-t': {data: 'Keep me', channel: null, locale: null, attribute_code: 'title|uuid-t'},
    };
    const result = populateCategory(makeCategory({attributes: existing}), template, [], []);
    expect(result.attributes['title|uuid-t'].data).toBe('Keep me');
  });

  it('removes deprecated attribute_codes key', () => {
    const category = makeCategory({
      attributes: {
        attribute_codes: {data: 'legacy', channel: null, locale: null, attribute_code: 'attribute_codes'},
      },
    });
    const result = populateCategory(category, null, [], []);
    expect(result.attributes).not.toHaveProperty('attribute_codes');
  });

  it('populates scopable+localizable attribute for each channel/locale combination', () => {
    const attribute = makeAttribute({
      code: 'desc',
      uuid: 'uuid-d',
      type: 'text',
      is_scopable: true,
      is_localizable: true,
    });
    const template = {uuid: 'tmpl', code: 'tpl', labels: {}, category_tree_identifier: 1, attributes: [attribute]};
    const result = populateCategory(makeCategory({attributes: {}}), template, ['ecommerce'], ['en_US', 'fr_FR']);
    expect(result.attributes).toHaveProperty('desc|uuid-d|ecommerce|en_US');
    expect(result.attributes).toHaveProperty('desc|uuid-d|ecommerce|fr_FR');
  });
});

describe('convertCategoryImageAttributeValueDataToFileInfo', () => {
  it('returns null when value is null', () => {
    expect(convertCategoryImageAttributeValueDataToFileInfo(null)).toBeNull();
  });

  it('maps snake_case fields to camelCase FileInfo', () => {
    const valueData: CategoryImageAttributeValueData = {
      size: 1024,
      file_path: '/path/to/file.jpg',
      mime_type: 'image/jpeg',
      extension: 'jpg',
      original_filename: 'photo.jpg',
    };
    expect(convertCategoryImageAttributeValueDataToFileInfo(valueData)).toEqual({
      size: 1024,
      filePath: '/path/to/file.jpg',
      mimeType: 'image/jpeg',
      extension: 'jpg',
      originalFilename: 'photo.jpg',
    });
  });

  it('handles missing optional fields', () => {
    const valueData: CategoryImageAttributeValueData = {
      file_path: '/path/to/file.jpg',
      original_filename: 'photo.jpg',
    };
    const result = convertCategoryImageAttributeValueDataToFileInfo(valueData);
    expect(result).toMatchObject({filePath: '/path/to/file.jpg', originalFilename: 'photo.jpg'});
    expect(result?.size).toBeUndefined();
    expect(result?.mimeType).toBeUndefined();
    expect(result?.extension).toBeUndefined();
  });
});

describe('convertFileInfoToCategoryImageAttributeValueData', () => {
  it('returns null when file is null', () => {
    expect(convertFileInfoToCategoryImageAttributeValueData(null)).toBeNull();
  });

  it('maps camelCase FileInfo to snake_case value data', () => {
    const file = {
      size: 2048,
      filePath: '/uploads/image.png',
      mimeType: 'image/png',
      extension: 'png',
      originalFilename: 'image.png',
    };
    expect(convertFileInfoToCategoryImageAttributeValueData(file)).toEqual({
      size: 2048,
      file_path: '/uploads/image.png',
      mime_type: 'image/png',
      extension: 'png',
      original_filename: 'image.png',
    });
  });
});
