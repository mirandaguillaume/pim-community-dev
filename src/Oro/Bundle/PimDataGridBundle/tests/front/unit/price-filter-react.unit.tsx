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
      this.criteriaValueSelectors = {currency: 'input[name="currency_currency"]', value: 'input[name="value"]'};
      const $el: any = {val: jest.fn(() => 'EUR')};
      this.$ = jest.fn(() => $el);
      this._$el = $el;
    }
    const proto = (NumberFilterReact as any).prototype;
    proto._renderReact = jest.fn();
    proto._getOperatorChoices = jest.fn(() => ({'1': '=', '2': '>'}));
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
      React.createElement('div', {'data-variant': props.variantClass, 'data-selected-option': props.selectedOption}),
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
    const spy = jest.spyOn(filter, '_renderReact');
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
});
