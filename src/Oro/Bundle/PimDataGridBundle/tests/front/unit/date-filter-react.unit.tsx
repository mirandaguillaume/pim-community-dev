// Unit test for the `date-filter-react` Backbone/React FILTER BRIDGE.
//
// The bridge is `export default DateFilter.extend({...})`: it inherits the legacy `DateFilter` and
// overrides only the markup + operator methods, wiring the React `DateFilterCriteria` popup via
// `ReactDOM.render`. None of its legacy AMD deps (`oro/*`) resolve in the Jest/Stryker sandbox, so we
// mock each BEFORE importing the bridge (jest.mock is hoisted). The `backboneExtend` technique (from
// react-filter-base.unit.tsx) chains the subclass prototype off the CALLING class so the bridge's
// `Base.prototype.method.call(this)` super-calls resolve.

// --- oro/datafilter/abstract-filter ---------------------------------------------------------------
jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter(this: any) {}
    (AbstractFilter as any).prototype.render = jest.fn();
    (AbstractFilter as any).prototype.remove = jest.fn(function (this: any) {
      return this;
    });
    return AbstractFilter;
  },
  {virtual: true}
);

// --- oro/datafilter/choice-filter -----------------------------------------------------------------
// The bridge super-calls `ChoiceFilter.prototype._showCriteria/_hideCriteria/_onValueUpdated`.
jest.mock(
  'oro/datafilter/choice-filter',
  () => {
    function ChoiceFilter(this: any) {}
    (ChoiceFilter as any).prototype._showCriteria = jest.fn();
    (ChoiceFilter as any).prototype._hideCriteria = jest.fn();
    (ChoiceFilter as any).prototype._onValueUpdated = jest.fn();
    return ChoiceFilter;
  },
  {virtual: true}
);

// --- oro/datafilter/date-filter -------------------------------------------------------------------
// The BASE the bridge extends. Its constructor seeds the instance fields the bridge reads; its
// prototype carries the inherited methods the bridge calls (`_getDisplayValue`, `_getCriteriaHint`,
// `_getOperatorChoices`, `_getInputValue`, `_displayFilterType`); it exposes the Backbone `.extend`.
jest.mock(
  'oro/datafilter/date-filter',
  () => {
    function DateFilter(this: any) {
      this.el = document.createElement('div');
      this.emptyValue = {type: 1, value: {start: '', end: ''}};
      this.showLabel = false;
      this.label = 'Created';
      this.canDisable = false;
      this.inputClass = 'AknTextField';
      this.datetimepickerOptions = {format: 'yyyy-MM-dd'};
      this.criteriaValueSelectors = {type: '.AknActionButton-highlight', value: {start: '.from', end: '.to'}};
      // A chainable jQuery-like handle so the `_onValueUpdated` empty-branch hide chain can be asserted.
      const chain: any = {};
      chain.hide = jest.fn(() => chain);
      chain.end = jest.fn(() => chain);
      chain.find = jest.fn(() => chain);
      this._chain = chain;
      this.$el = {find: jest.fn(() => chain)};
    }

    const proto = (DateFilter as any).prototype;
    proto._getDisplayValue = jest.fn(() => ({type: 1, value: {start: '2020-01-01', end: '2020-12-31'}}));
    proto._getCriteriaHint = jest.fn(() => 'the-hint');
    proto._getOperatorChoices = jest.fn(() => ({
      '1': 'between',
      '2': 'not between',
      '3': 'more than',
      '4': 'less than',
    }));
    proto._getInputValue = jest.fn((selector: string) => (selector === '.from input' ? 'start-val' : 'end-val'));
    proto._displayFilterType = jest.fn();

    function backboneExtend(this: any, protoOverrides: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, protoOverrides);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (DateFilter as any).extend = backboneExtend;

    return DateFilter;
  },
  {virtual: true}
);

// --- oro/translator -------------------------------------------------------------------------------
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

// --- ./DateFilterCriteria -------------------------------------------------------------------------
// Stub the popup so render() mounts something assertable WITHOUT the real Datepicker/DSM deps. Reflects
// the props the bridge forwards into data-attributes so `_renderReact` prop mutants die.
jest.mock('../../../Resources/public/js/datafilter/filter/DateFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        className: 'criteria-stub',
        'data-selected-operator': String(props.selectedOperator),
        'data-is-open': String(props.isOpen),
        'data-criteria-hint': String(props.criteriaHint),
        'data-label': String(props.label),
        'data-update-label': String(props.updateLabel),
        'data-operator-label': String(props.operatorLabel),
        'data-input-class': String(props.inputClass),
        'data-from': String(props.from),
        'data-to': String(props.to),
        'data-operator-choices': JSON.stringify(props.operatorChoices),
      }),
  };
});

import AbstractFilter from 'oro/datafilter/abstract-filter';
import ChoiceFilter from 'oro/datafilter/choice-filter';
import Bridge from '../../../Resources/public/js/datafilter/filter/date-filter-react';

const stub = (filter: any): HTMLElement | null => filter.el.querySelector('.criteria-stub');
const dateProto = (): any => jest.requireMock('oro/datafilter/date-filter').prototype;

beforeEach(() => {
  jest.clearAllMocks();
});

describe('render()', () => {
  test('chains to AbstractFilter.render, defaults the operator from emptyValue, mounts React, applies the initial filter-type, and returns this', () => {
    const filter: any = new Bridge();

    const result = filter.render();

    expect((AbstractFilter as any).prototype.render).toHaveBeenCalledTimes(1);
    expect(filter._selectedOperator).toBe(1); // seeded from emptyValue.type
    expect(stub(filter)).not.toBeNull();
    expect(dateProto()._displayFilterType).toHaveBeenCalledWith(1);
    expect(result).toBe(filter);
  });

  test('keeps a preset operator instead of overwriting it from emptyValue', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 3;

    filter.render();

    expect(filter._selectedOperator).toBe(3);
    expect(dateProto()._displayFilterType).toHaveBeenCalledWith(3);
  });
});

describe('_renderReact()', () => {
  test('mounts the stub and forwards operator (as string), open, hint, range and operator choices', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 3;
    filter._criteriaOpen = true;

    filter._renderReact();

    const node = stub(filter)!;
    expect(node.getAttribute('data-selected-operator')).toBe('3'); // numeric operator coerced to string
    expect(node.getAttribute('data-is-open')).toBe('true');
    expect(node.getAttribute('data-criteria-hint')).toBe('the-hint');
    expect(node.getAttribute('data-from')).toBe('2020-01-01');
    expect(node.getAttribute('data-to')).toBe('2020-12-31');
    expect(node.getAttribute('data-input-class')).toBe('AknTextField');
    expect(node.getAttribute('data-update-label')).toBe('pim_common.update');
    expect(node.getAttribute('data-operator-choices')).toBe(
      JSON.stringify({'1': 'between', '2': 'not between', '3': 'more than', '4': 'less than'})
    );
  });
});

describe('_renderCriteria()', () => {
  test('is a no-op that returns this and mounts no legacy popup', () => {
    const filter: any = new Bridge();

    const result = filter._renderCriteria();

    expect(result).toBe(filter);
    expect(filter.el.childElementCount).toBe(0);
  });
});

describe('_updateCriteriaHint()', () => {
  test('re-renders React and returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    expect(filter._updateCriteriaHint()).toBe(filter);
    expect(spy).toHaveBeenCalledTimes(1);
  });
});

describe('_showCriteria()', () => {
  test('opens, chains to the base show, re-renders, and returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._showCriteria();

    expect(filter._criteriaOpen).toBe(true);
    expect((ChoiceFilter as any).prototype._showCriteria).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(stub(filter)!.getAttribute('data-is-open')).toBe('true');
    expect(result).toBe(filter);
  });
});

describe('_hideCriteria()', () => {
  test('closes, chains to the base hide, re-renders, and returns this', () => {
    const filter: any = new Bridge();
    filter._criteriaOpen = true;
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._hideCriteria();

    expect(filter._criteriaOpen).toBe(false);
    expect((ChoiceFilter as any).prototype._hideCriteria).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter);
  });
});

describe('_updateCriteriaSelectorPosition()', () => {
  test('is a no-op that returns this without re-rendering (positioning owned by the hook)', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    expect(filter._updateCriteriaSelectorPosition()).toBe(filter);
    expect(spy).not.toHaveBeenCalled();
  });
});

describe('_highlightDropdown()', () => {
  test('records the operator as React state, re-renders, and returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._highlightDropdown(2);

    expect(filter._selectedOperator).toBe(2);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(stub(filter)!.getAttribute('data-selected-operator')).toBe('2');
    expect(result).toBe(filter);
  });
});

describe('_onSelectOperator()', () => {
  const eventFor = (value: string) => {
    const target = document.createElement('div');
    const choice = document.createElement('span');
    choice.className = 'operator_choice';
    choice.setAttribute('data-value', value);
    target.appendChild(choice);
    return {currentTarget: target, preventDefault: jest.fn()};
  };

  test('reads the clicked operator, re-renders, applies the filter-type, and prevents default', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');
    const e = eventFor('4');

    filter._onSelectOperator(e);

    expect(filter._selectedOperator).toBe('4');
    expect(spy).toHaveBeenCalledTimes(1);
    expect(dateProto()._displayFilterType).toHaveBeenCalledWith('4');
    expect(e.preventDefault).toHaveBeenCalledTimes(1);
  });
});

describe('_readDOMValue()', () => {
  test('is NOT overridden — the inherited DateFilter read yields the numeric-coerced operator + inputs', () => {
    const filter: any = new Bridge();

    // The bridge does not define _readDOMValue, so calling it uses the (mocked) DateFilter prototype.
    expect(Object.prototype.hasOwnProperty.call(Object.getPrototypeOf(filter), '_readDOMValue')).toBe(false);
  });
});

describe('_onValueUpdated()', () => {
  test('records the operator, chains to the base, and applies the filter-type for a value-bearing operator', () => {
    const filter: any = new Bridge();

    filter._onValueUpdated({type: 1, value: {start: 'a', end: 'b'}});

    expect(filter._selectedOperator).toBe(1);
    expect((ChoiceFilter as any).prototype._onValueUpdated).toHaveBeenCalledTimes(1);
    expect(dateProto()._displayFilterType).toHaveBeenCalledWith(1);
    expect(filter._chain.hide).not.toHaveBeenCalled();
  });

  test('hides the separator + both dates for the empty/not-empty operators', () => {
    const filter: any = new Bridge();

    filter._onValueUpdated({type: 'empty', value: {start: '', end: ''}});

    expect(filter._selectedOperator).toBe('empty');
    expect((ChoiceFilter as any).prototype._onValueUpdated).toHaveBeenCalledTimes(1);
    expect(filter._chain.hide).toHaveBeenCalledTimes(3); // separator, end, start
    expect(dateProto()._displayFilterType).not.toHaveBeenCalled();
  });
});

describe('remove()', () => {
  test('unmounts the React tree and chains to AbstractFilter.remove, returning its result', () => {
    const filter: any = new Bridge();
    filter.render();
    expect(stub(filter)).not.toBeNull();

    const result = filter.remove();

    expect(stub(filter)).toBeNull();
    expect((AbstractFilter as any).prototype.remove).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter);
  });
});
