import {getLabelFromAttribute, attributeFieldFactory} from './templateAttributesFactory';
import {Attribute} from '../../models';

jest.mock('./buildTextFieldAttribute', () => ({buildTextFieldAttribute: jest.fn(() => () => null)}));
jest.mock('./buildRichTextFieldAttribute', () => ({buildRichTextFieldAttribute: jest.fn(() => () => null)}));
jest.mock('./buildTextAreaFieldAttribute', () => ({buildTextAreaFieldAttribute: jest.fn(() => () => null)}));
jest.mock('./buildImageFieldAttribute', () => ({buildImageFieldAttribute: jest.fn(() => () => null)}));

const baseAttribute: Attribute = {
  uuid: 'attr-uuid',
  code: 'my_attr',
  type: 'text',
  order: 0,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'My Attribute', fr_FR: 'Mon Attribut'},
  template_uuid: 'tmpl-uuid',
};

describe('getLabelFromAttribute', () => {
  it('returns the label for the given locale', () => {
    expect(getLabelFromAttribute(baseAttribute, 'en_US')).toBe('My Attribute');
    expect(getLabelFromAttribute(baseAttribute, 'fr_FR')).toBe('Mon Attribut');
  });

  it('returns [code] fallback when the locale has no label', () => {
    expect(getLabelFromAttribute(baseAttribute, 'de_DE')).toBe('[my_attr]');
  });

  it('returns [code] fallback when labels is empty', () => {
    const attr = {...baseAttribute, labels: {}};
    expect(getLabelFromAttribute(attr, 'en_US')).toBe('[my_attr]');
  });
});

describe('attributeFieldFactory', () => {
  it('returns a React component for type "text"', () => {
    const Component = attributeFieldFactory({...baseAttribute, type: 'text'});
    expect(typeof Component).toBe('function');
  });

  it('returns a React component for type "richtext"', () => {
    const Component = attributeFieldFactory({...baseAttribute, type: 'richtext'});
    expect(typeof Component).toBe('function');
  });

  it('returns a React component for type "textarea"', () => {
    const Component = attributeFieldFactory({...baseAttribute, type: 'textarea'});
    expect(typeof Component).toBe('function');
  });

  it('returns a React component for type "image"', () => {
    const Component = attributeFieldFactory({...baseAttribute, type: 'image'});
    expect(typeof Component).toBe('function');
  });

  it('returns null for an unknown attribute type', () => {
    const Component = attributeFieldFactory({...baseAttribute, type: 'unknown' as any});
    expect(Component).toBeNull();
  });
});
