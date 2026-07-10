// Mock the legacy base + AbstractFilter so the bridge's inherited machinery is stubbed; render the child
// component to a prop-capturing div via a real react-dom mount (asserted through filter.el).
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
      this.widgetOptions = {multiple: false};
      this._value = {value: 'red'};
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

import AbstractFilter from 'oro/datafilter/abstract-filter';
import Bridge from '../../../Resources/public/js/datafilter/filter/select-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('select-filter-react', () => {
  test('render seeds _selectedValues from the model and mounts the React view', () => {
    const filter: any = new (Bridge as any)();
    filter.render();

    expect(filter._selectedValues).toEqual(['red']);
    const rendered = filter.el.querySelector('[data-multiple="false"]');
    expect(rendered).not.toBeNull();
    expect(rendered!.getAttribute('data-value')).toBe('red');
  });

  test('_normalizeToArray maps empty/string/array consistently', () => {
    const filter: any = new (Bridge as any)();
    expect(filter._normalizeToArray('')).toEqual([]);
    expect(filter._normalizeToArray(null)).toEqual([]);
    expect(filter._normalizeToArray('red')).toEqual(['red']);
    expect(filter._normalizeToArray(['red', 'blue'])).toEqual(['red', 'blue']);
  });

  test('_reactChoices sorts by label and prepends the All option when populateDefault', () => {
    const filter: any = new (Bridge as any)();
    const result = filter._reactChoices();
    expect(result[0]).toEqual({value: '', label: 'All'});
    // Blue < Red alphabetically
    expect(result.slice(1).map((c: any) => c.value)).toEqual(['blue', 'red']);
  });

  test('_readDOMValue returns the first selected value (single), empty when none', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedValues = ['blue'];
    expect(filter._readDOMValue()).toEqual({value: 'blue'});
    filter._selectedValues = [];
    expect(filter._readDOMValue()).toEqual({value: ''});
  });

  test('_onReactChange stores the values, re-renders, and pushes the formatted value', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    filter._onReactChange(['blue']);

    expect(filter._selectedValues).toEqual(['blue']);
    expect(renderSpy).toHaveBeenCalled();
    expect(filter.setValue).toHaveBeenCalledWith({value: 'blue', raw: true});
  });

  test('_writeDOMValue syncs state from an external value and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    filter._writeDOMValue({value: 'red'});

    expect(filter._selectedValues).toEqual(['red']);
    expect(renderSpy).toHaveBeenCalled();
  });

  test('_onValueUpdated syncs state, defers to the base, and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const renderSpy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    const newValue = {value: 'blue'};
    filter._onValueUpdated(newValue, {value: 'red'});

    expect(filter._selectedValues).toEqual(['blue']);
    expect((AbstractFilter as any).prototype._onValueUpdated).toHaveBeenCalledWith(newValue, {value: 'red'});
    expect(renderSpy).toHaveBeenCalled();
  });

  test('remove unmounts React then defers to AbstractFilter.remove', () => {
    const filter: any = new (Bridge as any)();
    filter.render();
    expect(filter.el.childNodes.length).toBeGreaterThan(0);

    filter.remove();
    expect(filter.el.childNodes.length).toBe(0);
    expect((AbstractFilter as any).prototype.remove).toHaveBeenCalled();
  });
});
