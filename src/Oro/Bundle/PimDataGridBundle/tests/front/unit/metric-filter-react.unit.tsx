// Mock the React base so the bridge's inherited render/operator machinery is stubbed.
const mockFetchAll = jest.fn(() => Promise.resolve([]));

jest.mock(
  'oro/datafilter/number-filter-react',
  () => {
    function NumberFilterReact(this: any) {
      this.el = document.createElement('div');
      this.label = 'Weight';
      this.family = 'weight';
      // Populated by the FilterTypeRegistry metadata merge in production (see AbstractFilter::getMetadata
      // → ChoiceView($data=operatorType, $value=(string)operatorType, $label=translated symbol, e.g. '=').
      this.choices = [{value: '3', label: '=', data: 3}];
      this._selectedOperator = '1';
      this._value = {type: '1', value: '10', unit: 'KILOGRAM'};
      this.measurementFamily = {
        code: 'weight',
        units: [
          {code: 'GRAM', labels: {en_US: 'Gram'}},
          {code: 'KILOGRAM', labels: {en_US: 'Kilogram'}},
        ],
      };
      this.criteriaValueSelectors = {unit: 'input[name="metric_unit"]', value: 'input[name="value"]'};
    }
    const proto = (NumberFilterReact as any).prototype;
    proto.initialize = jest.fn();
    proto._renderReact = jest.fn();
    proto._getOperatorChoices = jest.fn(() => ({'1': '=', '2': '>'}));
    proto._getDisplayValue = function (this: any) {
      return this._value;
    };
    proto.getValue = function (this: any) {
      return this._value;
    };
    proto._readDOMValue = jest.fn(() => ({type: '1', value: '10'}));
    proto.render = jest.fn();
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
jest.mock('pim/i18n', () => ({getLabel: (labels: any, locale: string, code: string) => labels[locale] || code}), {
  virtual: true,
});
jest.mock('pim/user-context', () => ({get: () => 'en_US'}), {virtual: true});
jest.mock('pim/fetcher-registry', () => ({getFetcher: () => ({fetchAll: mockFetchAll})}), {virtual: true});
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
import Bridge from '../../../Resources/public/js/datafilter/filter/metric-filter-react';

beforeEach(() => {
  jest.clearAllMocks();
  mockFetchAll.mockReturnValue(Promise.resolve([]));
});

describe('metric-filter-react', () => {
  test('_onSelectUnit records the unit in state and re-renders', () => {
    const filter: any = new (Bridge as any)();
    const currentTarget = document.createElement('div');
    const choice = document.createElement('span');
    choice.className = 'unit_choice';
    choice.setAttribute('data-value', 'GRAM');
    currentTarget.appendChild(choice);
    const e = {currentTarget, preventDefault: jest.fn()};
    // _renderReact would call _getCriteriaHint → _getChoiceOption, unavailable on this minimal mock.
    const spy = jest.spyOn(filter, '_renderReact').mockImplementation(() => {});

    filter._onSelectUnit(e);

    expect(filter._selectedUnit).toBe('GRAM');
    expect(spy).toHaveBeenCalled();
    expect(e.preventDefault).toHaveBeenCalled();
  });

  test('_readDOMValue augments the inherited number value with the unit from state', () => {
    const filter: any = new (Bridge as any)();
    filter._selectedUnit = 'KILOGRAM';
    expect(filter._readDOMValue()).toEqual({type: '1', value: '10', unit: 'KILOGRAM'});
  });

  test('_onValueUpdated syncs _selectedUnit from the new value and defers to the base', () => {
    const filter: any = new (Bridge as any)();
    const newValue = {type: '1', value: '5', unit: 'GRAM'};
    const oldValue = {};

    filter._onValueUpdated(newValue, oldValue);

    expect(filter._selectedUnit).toBe('GRAM');
    expect((NumberFilterReact as any).prototype._onValueUpdated).toHaveBeenCalledWith(newValue, oldValue);
  });

  test('initialize fetches the measurement family async and re-renders once it resolves', async () => {
    const measures = [
      {code: 'weight', units: [{code: 'GRAM', labels: {en_US: 'Gram'}}]},
      {code: 'length', units: [{code: 'METER', labels: {en_US: 'Meter'}}]},
    ];
    mockFetchAll.mockReturnValue(Promise.resolve(measures));
    const filter: any = new (Bridge as any)();
    filter.measurementFamily = undefined;
    filter.render = jest.fn();

    filter.initialize();
    // Flush the fetchAll().then(...) microtask.
    await Promise.resolve();
    await Promise.resolve();

    expect(filter.measurementFamily).toEqual(measures[0]);
    expect(filter.render).toHaveBeenCalled();
  });
});
