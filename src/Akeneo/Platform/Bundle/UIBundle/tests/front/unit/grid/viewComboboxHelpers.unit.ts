import {
  toComboView,
  hasMore,
  mergeViewPages,
  ensureDefaultView,
  idToValue,
  valueToId,
  VIEW_PAGE_SIZE,
} from '../../../../Resources/public/js/grid/viewComboboxHelpers';

describe('toComboView', () => {
  test('keeps id/type and uses text when present', () => {
    expect(toComboView({id: 5, text: 'My view', type: 'public', label: 'My view'})).toEqual({
      id: 5,
      text: 'My view',
      type: 'public',
      label: 'My view',
    });
  });

  test('falls back to label when text is missing, then to empty string', () => {
    expect(toComboView({id: 1, label: 'Labelled'}).text).toBe('Labelled');
    expect(toComboView({id: 2}).text).toBe('');
  });
});

describe('hasMore', () => {
  test('uses the explicit more flag when provided', () => {
    expect(hasMore({more: true, results: []})).toBe(true);
    expect(hasMore({more: false, results: new Array(VIEW_PAGE_SIZE).fill(0)})).toBe(false);
  });

  test('falls back to the full-page heuristic when more is absent', () => {
    expect(hasMore({results: new Array(VIEW_PAGE_SIZE).fill(0)})).toBe(true);
    expect(hasMore({results: new Array(VIEW_PAGE_SIZE - 1).fill(0)})).toBe(false);
    expect(hasMore({})).toBe(false);
  });
});

describe('mergeViewPages', () => {
  test('appends a new page', () => {
    const merged = mergeViewPages([{id: 1, text: 'a'}], [{id: 2, text: 'b'}]);
    expect(merged.map(v => v.id)).toEqual([1, 2]);
  });

  test('de-duplicates ids that reappear across pages', () => {
    const merged = mergeViewPages(
      [
        {id: 1, text: 'a'},
        {id: 2, text: 'b'},
      ],
      [
        {id: 2, text: 'b'},
        {id: 3, text: 'c'},
      ]
    );
    expect(merged.map(v => v.id)).toEqual([1, 2, 3]);
  });
});

describe('ensureDefaultView', () => {
  const def = {id: 0, text: 'Default view'};

  test('prepends the default view when shouldShow is true', () => {
    expect(ensureDefaultView([{id: 1, text: 'a'}], def, true).map(v => v.id)).toEqual([0, 1]);
  });

  test('leaves the list untouched when shouldShow is false', () => {
    expect(ensureDefaultView([{id: 1, text: 'a'}], def, false).map(v => v.id)).toEqual([1]);
  });
});

describe('id <-> value', () => {
  test('round-trips, including the synthetic default id 0', () => {
    expect(idToValue(0)).toBe('0');
    expect(idToValue(42)).toBe('42');
    expect(valueToId('0')).toBe(0);
    expect(valueToId('42')).toBe(42);
  });
});
