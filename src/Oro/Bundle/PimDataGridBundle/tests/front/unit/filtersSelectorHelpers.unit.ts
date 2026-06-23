import {
  computeFilterState,
  mergeCategoryFilter,
  shouldReloadGridState,
  StatefulFilter,
  FilterState,
} from '../../../Resources/public/js/datafilter/filtersSelectorHelpers';

const filter = (
  over: Partial<{enabled: boolean; defaultEnabled: boolean; empty: boolean; value: {type: string; value: any}}> = {}
): StatefulFilter => {
  const {enabled = true, defaultEnabled = false, empty = false, value = {type: 'contains', value: 'x'}} = over;

  return {enabled, defaultEnabled, isEmpty: () => empty, getValue: () => value};
};

describe('computeFilterState', () => {
  test('enabled + non-empty → name maps to the filter value', () => {
    expect(computeFilterState({sku: filter({value: {type: 'contains', value: 'abc'}})})).toEqual({
      sku: {type: 'contains', value: 'abc'},
    });
  });

  test('enabled + empty + NOT default-on → __name: 1', () => {
    expect(computeFilterState({sku: filter({empty: true, defaultEnabled: false})})).toEqual({__sku: 1});
  });

  test('enabled + empty + default-on → absent (use the default)', () => {
    expect(computeFilterState({sku: filter({empty: true, defaultEnabled: true})})).toEqual({});
  });

  test('disabled + default-on → __name: 0', () => {
    expect(computeFilterState({sku: filter({enabled: false, defaultEnabled: true})})).toEqual({__sku: 0});
  });

  test('disabled + NOT default-on → absent', () => {
    expect(computeFilterState({sku: filter({enabled: false, defaultEnabled: false})})).toEqual({});
  });

  test('mixes all branches across several filters', () => {
    expect(
      computeFilterState({
        a: filter({value: {type: 'in', value: [1]}}),
        b: filter({empty: true, defaultEnabled: false}),
        c: filter({enabled: false, defaultEnabled: true}),
        d: filter({enabled: false, defaultEnabled: false}),
      })
    ).toEqual({a: {type: 'in', value: [1]}, __b: 1, __c: 0});
  });
});

describe('mergeCategoryFilter', () => {
  test('the category filter wins on a key conflict', () => {
    const merged = mergeCategoryFilter({a: 1, b: 2}, {b: 9});

    expect(merged).toEqual({a: 1, b: 9});
  });

  test('does not mutate either input', () => {
    const base: FilterState = {a: 1};
    const category: FilterState = {b: 2};

    mergeCategoryFilter(base, category);

    expect(base).toEqual({a: 1});
    expect(category).toEqual({b: 2});
  });
});

describe('shouldReloadGridState', () => {
  test('reloads when the state changed and not silent', () => {
    expect(shouldReloadGridState({a: 1}, {a: 2}, false)).toBe(true);
  });

  test('does NOT reload when unchanged, non-empty and not silent', () => {
    expect(shouldReloadGridState({a: 1}, {a: 1}, false)).toBe(false);
  });

  test('reloads when the current state is empty even if equal', () => {
    expect(shouldReloadGridState({}, {}, false)).toBe(true);
  });

  test('never reloads while silent, even when changed', () => {
    expect(shouldReloadGridState({a: 1}, {a: 2}, true)).toBe(false);
  });
});
