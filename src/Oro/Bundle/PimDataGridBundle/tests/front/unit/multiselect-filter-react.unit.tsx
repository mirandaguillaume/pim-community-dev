// Mock the React BASE (select-filter-react) so the multiselect bridge's inherited render/value machinery
// is stubbed — mirrors metric-filter-react.unit.tsx, which mocks its number-filter-react base. The base is
// imported by the SOURCE via the bare AMD specifier `oro/datafilter/select-filter-react`; jest can only
// resolve that through a {virtual:true} mock (there is no moduleNameMapper for `oro/datafilter/*`), so the
// bridge's own overrides are tested against this stub base. The inherited members (render, _renderReact,
// _normalizeToArray, _writeDOMValue, _onValueUpdated, remove, getCriteria, …) are exercised by PR1's
// select-filter-react.unit.tsx and are not this PR's mutation surface.
jest.mock(
  'oro/datafilter/select-filter-react',
  () => {
    function SelectFilterReact(this: any) {
      this.el = document.createElement('div');
      // The model value the "All" exclusion diffs against; overridden per-test.
      this._value = {value: ['red']};
      this.choices = [];
      this.populateDefault = true;
      this.placeholder = 'All';
      this.setValue = jest.fn();
    }
    const proto = (SelectFilterReact as any).prototype;
    // Plain function (not jest.fn) so its implementation is never at the mercy of a mock reset.
    proto.getValue = function (this: any) {
      return this._value;
    };
    proto._formatRawValue = jest.fn((v: any) => ({...v, raw: true}));
    // Inherited by the bridge (it does not override _renderReact); spied to assert re-render on change.
    proto._renderReact = jest.fn();
    function backboneExtend(this: any, o: any) {
      const P = this;
      function S(this: any) {
        P.apply(this, arguments);
      }
      S.prototype = Object.create(P.prototype);
      Object.assign(S.prototype, o);
      (S as any).extend = backboneExtend;
      return S;
    }
    (SelectFilterReact as any).extend = backboneExtend;
    return SelectFilterReact;
  },
  {virtual: true}
);

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});

import Bridge from '../../../Resources/public/js/datafilter/filter/multiselect-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('multiselect-filter-react', () => {
  test('widgetOptions.multiple is true, so the inherited _renderReact renders MultiSelectInput', () => {
    const filter: any = new (Bridge as any)();
    expect(filter.widgetOptions.multiple).toBe(true);
  });

  test('_readDOMValue returns the FULL array (not just the first element)', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedValues = ['red', 'blue'];
    expect(filter._readDOMValue()).toEqual({value: ['red', 'blue']});
    filter._selectedValues = [];
    expect(filter._readDOMValue()).toEqual({value: []});
  });

  test('_reactChoices flattens optgroups to leaf options, dedupes by value, translates, sorts, prepends All', () => {
    const filter: any = new (Bridge as any)();
    filter.choices = [
      {value: 'red', label: 'Red'},
      {
        label: 'Warm',
        value: [
          {value: 'orange', label: 'Orange'},
          {value: 'red', label: 'Red dup'},
        ],
      },
      {label: 'Cool', value: [{value: 'blue', label: 'Blue'}]},
    ];
    filter.populateDefault = true;
    filter.placeholder = 'All';

    const result = filter._reactChoices();

    // "All" prepended first.
    expect(result[0]).toEqual({value: '', label: 'All'});
    // Optgroups flattened to leaves, `red` deduped (first occurrence kept), sorted by label
    // (Blue < Orange < Red).
    expect(result.slice(1)).toEqual([
      {value: 'blue', label: 'Blue'},
      {value: 'orange', label: 'Orange'},
      {value: 'red', label: 'Red'},
    ]);
    // Regression guard: no option value is an object (an object value crashes DSM MultiSelectInput with
    // "Duplicate option value [object Object]").
    result.forEach((choice: any) => expect(typeof choice.value).toBe('string'));
  });

  describe('_applyAllExclusion (the "All"/`\'\'` mutual-exclusion)', () => {
    test('adding a second concrete option keeps both', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion(['red', 'blue'])).toEqual(['red', 'blue']);
    });

    test('clicking "All" while concrete options were selected collapses to [""]', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion(['red', 'blue', ''])).toEqual(['']);
    });

    test('adding a concrete option while "All" was active removes "All"', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['']};
      expect(filter._applyAllExclusion(['', 'red'])).toEqual(['red']);
    });

    test('deselecting everything falls back to [""]', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ['red']};
      expect(filter._applyAllExclusion([])).toEqual(['']);
    });

    test('initial empty-string model is treated as ["" ] previous value', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ''};
      expect(filter._applyAllExclusion(['red'])).toEqual(['red']);
    });

    // Kills the init-coercion mutant: only an empty-string model COMBINED with a `values` array that
    // contains '' distinguishes `'' === getValue().value ? [''] : …` from its mutated forms. Without the
    // coercion, previousValue would be the string '' (not ['']), changing the `addAll` diff and yielding
    // ['']; the coercion keeps addAll=false so "All" is merely dropped, leaving ['red'].
    test('empty-string model + a selection containing "All" drops "All" (kills the init-coercion mutant)', () => {
      const filter: any = new (Bridge as any)();
      filter._value = {value: ''};
      expect(filter._applyAllExclusion(['red', ''])).toEqual(['red']);
    });
  });

  test('_onReactChange applies the exclusion, stores state, re-renders, and pushes the formatted value', () => {
    const filter: any = new (Bridge as any)();
    filter._value = {value: ['red']};
    // user clicks "All" (''), which should collapse to ['']
    filter._onReactChange(['red', '']);

    expect(filter._selectedValues).toEqual(['']);
    expect(filter._renderReact).toHaveBeenCalled();
    expect(filter.setValue).toHaveBeenCalledWith({value: [''], raw: true});
  });
});
