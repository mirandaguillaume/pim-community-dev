import {
  resolveFilterModuleId,
  FILTER_MODULE_IDS,
  FILTER_TYPE_ALIASES,
} from '../../../Resources/public/js/datafilter/FilterTypeRegistry';

describe('FILTER_TYPE_ALIASES', () => {
  test('maps string → choice', () => expect(FILTER_TYPE_ALIASES['string']).toBe('choice'));
  test('maps choice → select', () => expect(FILTER_TYPE_ALIASES['choice']).toBe('select'));
  test('maps selectrow → select-row', () => expect(FILTER_TYPE_ALIASES['selectrow']).toBe('select-row'));
  test('maps multichoice → multiselect', () => expect(FILTER_TYPE_ALIASES['multichoice']).toBe('multiselect'));
  test('maps boolean → select', () => expect(FILTER_TYPE_ALIASES['boolean']).toBe('select'));
  test('maps identifier → identifier (no-op)', () => expect(FILTER_TYPE_ALIASES['identifier']).toBe('identifier'));
});

describe('FILTER_MODULE_IDS', () => {
  test('ajax-choice', () => expect(FILTER_MODULE_IDS['ajax-choice']).toBe('oro/datafilter/ajax-choice-filter'));
  test('choice', () => expect(FILTER_MODULE_IDS['choice']).toBe('oro/datafilter/choice-filter-react'));
  test('date', () => expect(FILTER_MODULE_IDS['date']).toBe('oro/datafilter/date-filter-react'));
  test('datetime', () => expect(FILTER_MODULE_IDS['datetime']).toBe('oro/datafilter/datetime-filter-react'));
  test('grouped-variant', () =>
    expect(FILTER_MODULE_IDS['grouped-variant']).toBe('oro/datafilter/grouped-variant-filter'));
  test('identifier', () => expect(FILTER_MODULE_IDS['identifier']).toBe('oro/datafilter/identifier-filter-react'));
  test('label_or_identifier', () =>
    expect(FILTER_MODULE_IDS['label_or_identifier']).toBe('oro/datafilter/label_or_identifier-filter'));
  test('metric', () => expect(FILTER_MODULE_IDS['metric']).toBe('oro/datafilter/metric-filter'));
  test('multiselect', () => expect(FILTER_MODULE_IDS['multiselect']).toBe('oro/datafilter/multiselect-filter'));
  test('none', () => expect(FILTER_MODULE_IDS['none']).toBe('oro/datafilter/none-filter'));
  test('number', () => expect(FILTER_MODULE_IDS['number']).toBe('oro/datafilter/number-filter-react'));
  test('parent', () => expect(FILTER_MODULE_IDS['parent']).toBe('oro/datafilter/parent-filter-react'));
  test('price', () => expect(FILTER_MODULE_IDS['price']).toBe('oro/datafilter/price-filter-react'));
  test('product_and_product_model_completeness', () =>
    expect(FILTER_MODULE_IDS['product_and_product_model_completeness']).toBe(
      'oro/datafilter/product_completeness-filter'
    ));
  test('product_category', () =>
    expect(FILTER_MODULE_IDS['product_category']).toBe('oro/datafilter/product_category-filter'));
  test('product_completeness', () =>
    expect(FILTER_MODULE_IDS['product_completeness']).toBe('oro/datafilter/product_completeness-filter'));
  test('product_scope', () => expect(FILTER_MODULE_IDS['product_scope']).toBe('oro/datafilter/product_scope-filter'));
  test('search', () => expect(FILTER_MODULE_IDS['search']).toBe('oro/datafilter/search-filter'));
  test('attribute_search', () => expect(FILTER_MODULE_IDS['attribute_search']).toBe('oro/datafilter/search-filter'));
  test('select', () => expect(FILTER_MODULE_IDS['select']).toBe('oro/datafilter/select-filter'));
  test('select-row', () => expect(FILTER_MODULE_IDS['select-row']).toBe('oro/datafilter/select-row-filter'));
  test('select2-choice', () =>
    expect(FILTER_MODULE_IDS['select2-choice']).toBe('oro/datafilter/select2-choice-filter-react'));
  test('select2-rest-choice', () =>
    expect(FILTER_MODULE_IDS['select2-rest-choice']).toBe('oro/datafilter/select2-rest-choice-filter-react'));
  test('text', () => expect(FILTER_MODULE_IDS['text']).toBe('oro/datafilter/text-filter-react'));
  test('uuid', () => expect(FILTER_MODULE_IDS['uuid']).toBe('oro/datafilter/uuid-filter-react'));
});

describe('resolveFilterModuleId', () => {
  describe('direct types (no alias)', () => {
    test('text → text-filter-react', () =>
      expect(resolveFilterModuleId('text')).toBe('oro/datafilter/text-filter-react'));
    test('date → date-filter-react', () =>
      expect(resolveFilterModuleId('date')).toBe('oro/datafilter/date-filter-react'));
    test('price → price-filter-react', () =>
      expect(resolveFilterModuleId('price')).toBe('oro/datafilter/price-filter-react'));
    test('search → search-filter', () => expect(resolveFilterModuleId('search')).toBe('oro/datafilter/search-filter'));
    test('uuid → uuid-filter-react', () =>
      expect(resolveFilterModuleId('uuid')).toBe('oro/datafilter/uuid-filter-react'));
    test('product_scope → product_scope-filter', () =>
      expect(resolveFilterModuleId('product_scope')).toBe('oro/datafilter/product_scope-filter'));
    test('product_category → product_category-filter', () =>
      expect(resolveFilterModuleId('product_category')).toBe('oro/datafilter/product_category-filter'));
    test('product_completeness → product_completeness-filter', () =>
      expect(resolveFilterModuleId('product_completeness')).toBe('oro/datafilter/product_completeness-filter'));
    test('attribute_search → search-filter (shared module)', () =>
      expect(resolveFilterModuleId('attribute_search')).toBe('oro/datafilter/search-filter'));
  });

  describe('aliased types', () => {
    test('string → choice-filter-react', () =>
      expect(resolveFilterModuleId('string')).toBe('oro/datafilter/choice-filter-react'));
    test('choice → select-filter', () => expect(resolveFilterModuleId('choice')).toBe('oro/datafilter/select-filter'));
    test('boolean → select-filter (same as choice)', () =>
      expect(resolveFilterModuleId('boolean')).toBe('oro/datafilter/select-filter'));
    test('selectrow → select-row-filter', () =>
      expect(resolveFilterModuleId('selectrow')).toBe('oro/datafilter/select-row-filter'));
    test('multichoice → multiselect-filter', () =>
      expect(resolveFilterModuleId('multichoice')).toBe('oro/datafilter/multiselect-filter'));
    test('identifier (alias no-op) → identifier-filter', () =>
      expect(resolveFilterModuleId('identifier')).toBe('oro/datafilter/identifier-filter-react'));
  });

  describe('error handling', () => {
    test('unknown type throws', () => {
      expect(() => resolveFilterModuleId('nonexistent_type')).toThrow(
        'FilterTypeRegistry: no module registered for filter type "nonexistent_type"'
      );
    });

    test('unknown type that aliases to nothing throws with alias info', () => {
      expect(() => resolveFilterModuleId('unknown_type')).toThrow('"unknown_type"');
    });

    test('empty string throws', () => {
      expect(() => resolveFilterModuleId('')).toThrow('FilterTypeRegistry');
    });
  });
});
