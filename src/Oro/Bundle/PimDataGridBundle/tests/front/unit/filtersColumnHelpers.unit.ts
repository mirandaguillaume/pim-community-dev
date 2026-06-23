import {
  mergeAddedFilters,
  filterBySearchTerm,
  groupFilters,
  GridFilter,
} from '../../../Resources/public/js/datafilter/filtersColumnHelpers';

const f = (over: Partial<GridFilter> = {}): GridFilter => ({
  group: 'System',
  label: 'Label',
  name: 'name',
  enabled: false,
  ...over,
});

describe('mergeAddedFilters', () => {
  test('concatenates original then added and dedupes by name (first occurrence wins)', () => {
    const result = mergeAddedFilters([f({name: 'a'})], [f({name: 'a'}), f({name: 'b'})], []);

    expect(result.map(x => x.name)).toEqual(['a', 'b']);
  });

  test('marks a filter enabled when its name is in the active set, leaves the others', () => {
    const result = mergeAddedFilters([f({name: 'a', enabled: false})], [f({name: 'b', enabled: false})], ['a']);

    expect(result.find(x => x.name === 'a')!.enabled).toBe(true);
    expect(result.find(x => x.name === 'b')!.enabled).toBe(false);
  });
});

describe('filterBySearchTerm', () => {
  test('matches the label case-insensitively', () => {
    const result = filterBySearchTerm([f({name: 'sku', label: 'SKU'}), f({name: 'color', label: 'Color'})], 'COL');

    expect(result.map(x => x.name)).toEqual(['color']);
  });

  test('matches the name when the label does not', () => {
    const result = filterBySearchTerm([f({name: 'price_eur', label: 'Tarif'})], 'price');

    expect(result.map(x => x.name)).toEqual(['price_eur']);
  });

  test('returns nothing when neither label nor name match', () => {
    expect(filterBySearchTerm([f({name: 'sku', label: 'SKU'})], 'zzz')).toEqual([]);
  });
});

describe('groupFilters', () => {
  test('groups by the group property', () => {
    const result = groupFilters([
      f({name: 'a', group: 'Marketing'}),
      f({name: 'b', group: 'Marketing'}),
      f({name: 'c', group: 'Technical'}),
    ]);

    expect(Object.keys(result)).toEqual(['Marketing', 'Technical']);
    expect(result.Marketing.map(x => x.name)).toEqual(['a', 'b']);
  });

  test('falls back to the System group when group is empty', () => {
    const result = groupFilters([f({name: 'a', group: ''})]);

    expect(result.System.map(x => x.name)).toEqual(['a']);
  });
});
