// Mirrors select-filter-react.unit.tsx: mock the legacy base (SelectFilter) + AbstractFilter so the
// inherited machinery is stubbed; render the child to a prop-capturing div via a real react-dom mount.
// The multi bridge extends the REAL select-filter-react (PR1), so only SelectFilter/AbstractFilter/DSM
// are mocked — select-filter-react and its overrides run for real.
jest.mock(
  'oro/datafilter/select-filter',
  () => {
    function SelectFilter(this: any) {
      this.el = document.createElement('div');
      this.choices = [
        {value: 'red', label: 'Red'},
        {value: 'blue', label: 'Blue'},
      ];
      this.placeholder = 'All';
      this.populateDefault = true;
      this.showLabel = true;
      this.label = 'Color';
      this.canDisable = true;
      this.nullLink = '#null';
      // NOTE: do NOT set this.widgetOptions here — the multi bridge declares widgetOptions:{multiple:true}
      // on its prototype; an instance property set in this constructor would SHADOW it and force single.
      this._value = {value: ['red']};
      this.setValue = jest.fn();
    }
    const proto = (SelectFilter as any).prototype;
    proto.getValue = function (this: any) {
      return this._value;
    };
    proto._formatRawValue = jest.fn((v: any) => ({...v, raw: true}));
    proto.disable = jest.fn();
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
    (SelectFilter as any).extend = backboneExtend;
    return SelectFilter;
  },
  {virtual: true}
);

jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter() {}
    (AbstractFilter as any).prototype.render = function (this: any) {
      return this;
    };
    (AbstractFilter as any).prototype.remove = jest.fn(function (this: any) {
      return this;
    });
    (AbstractFilter as any).prototype._onValueUpdated = jest.fn();
    return AbstractFilter;
  },
  {virtual: true}
);

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});

// The inherited _renderReact wraps its mount in ThemeProvider + DependenciesProvider (DSM theme context).
// Stub them to pass-through providers so the mounted SelectFilterCriteria stand-in lands in `filter.el`.
jest.mock('styled-components', () => ({ThemeProvider: ({children}: any) => children}));
jest.mock('akeneo-design-system', () => ({pimTheme: {}}));
jest.mock('@akeneo-pim-community/legacy-bridge', () => ({DependenciesProvider: ({children}: any) => children}));

jest.mock('../../../Resources/public/js/datafilter/filter/SelectFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        'data-multiple': String(props.multiple),
        'data-value': (props.value || []).join(','),
        'data-choices': JSON.stringify(props.choices),
      }),
  };
});

import Bridge from '../../../Resources/public/js/datafilter/filter/multiselect-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('multiselect-filter-react', () => {
  test('render seeds _selectedValues from the model and mounts the MULTI React view', () => {
    const filter: any = new (Bridge as any)();
    filter.render();

    expect(filter._selectedValues).toEqual(['red']);
    const rendered = filter.el.querySelector('[data-multiple="true"]');
    expect(rendered).not.toBeNull();
    expect(rendered!.getAttribute('data-value')).toBe('red');
  });

  test('_readDOMValue returns the FULL array (not just the first element)', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedValues = ['red', 'blue'];
    expect(filter._readDOMValue()).toEqual({value: ['red', 'blue']});
    filter._selectedValues = [];
    expect(filter._readDOMValue()).toEqual({value: []});
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
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    // user clicks "All" (''), which should collapse to ['']
    filter._onReactChange(['red', '']);

    expect(filter._selectedValues).toEqual(['']);
    expect(renderSpy).toHaveBeenCalled();
    expect(filter.setValue).toHaveBeenCalledWith({value: [''], raw: true});
  });
});
