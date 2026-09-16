// Mock the React base so the bridge's inherited render/operator machinery is stubbed.
jest.mock(
  'oro/datafilter/number-filter-react',
  () => {
    function NumberFilterReact(this: any) {
      this.el = document.createElement('div');
      this.label = 'Price';
      this.currencies = {USD: 'USD', EUR: 'EUR'};
      this._selectedOperator = '1';
      this._value = {type: '1', value: '10', currency: 'EUR'};
      this.showLabel = false;
      this.canDisable = false;
      this.placeholder = 'All';
      this.criteriaValueSelectors = {currency: 'input[name="currency_currency"]', value: 'input[name="value"]'};
      const $el: any = {val: jest.fn(() => 'EUR')};
      this.$ = jest.fn(() => $el);
      this._$el = $el;
    }
    const proto = (NumberFilterReact as any).prototype;
    proto._renderReact = jest.fn();
    proto._getOperatorChoices = jest.fn(() => ({'1': '=', '2': '>'}));
    // Plain function (not jest.fn) so `jest.clearAllMocks()` in beforeEach never wipes the implementation.
    proto._getChoiceOption = function (type: string) {
      return {label: String(type)};
    };
    proto._getDisplayValue = function (this: any) {
      return this._value;
    };
    proto.getValue = function (this: any) {
      return this._value;
    };
    proto._readDOMValue = jest.fn(() => ({type: '1', value: '10'}));
    proto._writeDOMValue = jest.fn();
    proto._getInputValue = jest.fn(() => 'EUR');
    proto._highlightDropdown = jest.fn();
    proto._onValueUpdated = jest.fn();
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
    (NumberFilterReact as any).extend = backboneExtend;
    return NumberFilterReact;
  },
  {virtual: true}
);

jest.mock('oro/translator', () => (k: string) => k, {virtual: true});
jest.mock('../../../Resources/public/js/datafilter/filter/NumberUnitFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        'data-variant': props.variantClass,
        'data-selected-option': props.selectedOption,
        'data-options': JSON.stringify(props.optionChoices),
      }),
  };
});

import NumberFilterReact from 'oro/datafilter/number-filter-react';
import Bridge from '../../../Resources/public/js/datafilter/filter/price-filter-react';

beforeEach(() => jest.clearAllMocks());

describe('price-filter-react', () => {
  test('_onSelectCurrency records the currency in state and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const currentTarget = document.createElement('div');
    const choice = document.createElement('span');
    choice.className = 'currency_choice';
    choice.setAttribute('data-value', 'USD');
    currentTarget.appendChild(choice);
    const e = {currentTarget, preventDefault: jest.fn()};
    const spy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});
    filter._onSelectCurrency(e);
    expect(filter._selectedCurrency).toBe('USD');
    expect(spy).toHaveBeenCalled();
    expect(e.preventDefault).toHaveBeenCalled();
  });

  test('_readDOMValue augments the inherited number value with the currency from state', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedCurrency = 'EUR';
    expect(filter._readDOMValue()).toEqual({type: '1', value: '10', currency: 'EUR'});
  });

  test('_onValueUpdated syncs _selectedCurrency from the new value and defers to the base', () => {
    const filter: any = new (Bridge as any)();
    const newValue = {type: '1', value: '5', currency: 'EUR'};
    const oldValue = {};

    filter._onValueUpdated(newValue, oldValue);

    expect(filter._selectedCurrency).toBe('EUR');
    expect((NumberFilterReact as any).prototype._onValueUpdated).toHaveBeenCalledWith(newValue, oldValue);
  });

  test('_renderReact computes _selectedCurrency from the display value and mounts the shared criteria', () => {
    const filter: any = new (Bridge as any)();

    filter._renderReact();

    // Guard fell through to `this._getDisplayValue().currency` (mock `_value.currency` is 'EUR').
    expect(filter._selectedCurrency).toBe('EUR');
    const rendered = filter.el.querySelector('[data-variant="currencyfilter"]');
    expect(rendered).not.toBeNull();
    expect(rendered!.getAttribute('data-selected-option')).toBe('EUR');
    expect(rendered!.getAttribute('data-options')).toContain('{"value":"EUR","label":"EUR"}');
  });

  test('_getCriteriaHint returns "operator value currency" for a value-bearing filter', () => {
    const filter: any = new (Bridge as any)();
    filter._getChoiceOption = jest.fn(() => ({label: '='}));
    filter._value = {type: '1', value: '10', currency: 'EUR'};

    expect(filter._getCriteriaHint()).toBe('= 10 EUR');
  });

  test('_getCriteriaHint returns the choice label for the empty operator', () => {
    const filter: any = new (Bridge as any)();
    filter._value = {type: 'empty', value: '', currency: 'EUR'};

    expect(filter._getCriteriaHint()).toBe(filter._getChoiceOption('empty').label);
  });
});
