// Unit test for the `choice-filter-react` Backbone/React FILTER BRIDGE.
//
// The bridge is `export default ChoiceFilter.extend({...})`: it inherits the legacy `ChoiceFilter`
// and overrides only the markup + operator methods, wiring the React `ChoiceFilterCriteria` popup via
// `ReactDOM.render`. None of its legacy AMD dependencies (`oro/*`) resolve in the Jest/Stryker
// sandbox, so we mock each one BEFORE importing the bridge (jest.mock is hoisted).
//
// The base mocks reuse the `backboneExtend` technique from `react-filter-base.unit.tsx`: `.extend`
// chains the subclass prototype off the CALLING class (`Parent = this`) so the bridge's
// `Base.prototype.method.call(this)` super-calls resolve and inheritance is preserved.

// --- oro/datafilter/abstract-filter ------------------------------------------------------------
// `render()` chains to `AbstractFilter.prototype.render`; `remove()` chains to
// `AbstractFilter.prototype.remove`. Provide both as spies (remove returns `this`, as Backbone does).
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

// --- oro/datafilter/text-filter --------------------------------------------------------------
// `_onValueUpdated()` defers to `TextFilter.prototype._onValueUpdated`.
jest.mock(
  'oro/datafilter/text-filter',
  () => {
    function TextFilter(this: any) {}
    (TextFilter as any).prototype._onValueUpdated = jest.fn();
    return TextFilter;
  },
  {virtual: true}
);

// --- oro/datafilter/choice-filter ------------------------------------------------------------
// The BASE the bridge extends. Its constructor seeds the instance defaults the bridge reads, its
// prototype carries every method the bridge calls via `this.*` or `ChoiceFilter.prototype.*`, and it
// exposes the Backbone-style `.extend` so `ChoiceFilter.extend({...})` produces a real subclass.
jest.mock(
  'oro/datafilter/choice-filter',
  () => {
    function ChoiceFilter(this: any) {
      this.el = document.createElement('div');
      this.emptyValue = {type: 'in'};
      this.emptyChoice = true;
      this.showLabel = false;
      this.label = 'Name';
      this.canDisable = false;
      this.criteriaValueSelectors = {value: '.value'};
      // A stable jQuery-like handle so tests can assert show/hide on the value field.
      this._$obj = {hide: jest.fn(), show: jest.fn()};
      this.$ = jest.fn(() => this._$obj);
    }

    const proto = (ChoiceFilter as any).prototype;
    proto._toggleListSelection = jest.fn();
    proto._toggleInput = jest.fn();
    proto._getCriteriaHint = jest.fn(() => 'the-hint');
    proto._getOperatorChoices = jest.fn(() => ({in: 'In', empty: 'Empty'}));
    proto._getInputValue = jest.fn(() => 'typed-value');
    proto._showCriteria = jest.fn();
    proto._hideCriteria = jest.fn();

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
    (ChoiceFilter as any).extend = backboneExtend;

    return ChoiceFilter;
  },
  {virtual: true}
);

// --- oro/translator --------------------------------------------------------------------------
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

// --- ./ChoiceFilterCriteria ------------------------------------------------------------------
// Stub the popup component so `render()` mounts something assertable WITHOUT pulling the real DSM
// deps. It reflects the props the bridge passes into data-attributes so `_renderReact` prop mutants
// die. Uses a local `require('react')` — the factory may not reference out-of-scope bindings.
jest.mock('../../../Resources/public/js/datafilter/filter/ChoiceFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        className: 'criteria-stub',
        'data-selected-operator': String(props.selectedOperator),
        'data-is-open': String(props.isOpen),
        'data-criteria-hint': String(props.criteriaHint),
        'data-show-label': String(props.showLabel),
        'data-label': String(props.label),
        'data-can-disable': String(props.canDisable),
        'data-empty-choice': String(props.emptyChoice),
        'data-operator-label': String(props.operatorLabel),
        'data-update-label': String(props.updateLabel),
        'data-operator-choices': JSON.stringify(props.operatorChoices),
      }),
  };
});

import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import ChoiceFilter from 'oro/datafilter/choice-filter';
import Bridge from '../../../Resources/public/js/datafilter/filter/choice-filter-react';

const stub = (filter: any): HTMLElement | null => filter.el.querySelector('.criteria-stub');

beforeEach(() => {
  jest.clearAllMocks();
});

describe('render()', () => {
  test('chains to AbstractFilter.render, defaults the operator from emptyValue, mounts the React popup, and returns this', () => {
    const filter: any = new Bridge();

    const result = filter.render();

    expect((AbstractFilter as any).prototype.render).toHaveBeenCalledTimes(1);
    expect(filter._selectedOperator).toBe('in'); // seeded from emptyValue.type
    expect(stub(filter)).not.toBeNull(); // React tree mounted into this.el
    expect(result).toBe(filter);
  });

  test('for the default `in` operator toggles the list ON and the input OFF', () => {
    const filter: any = new Bridge();

    filter.render();

    expect((ChoiceFilter as any).prototype._toggleListSelection).toHaveBeenCalledWith(true);
    expect((ChoiceFilter as any).prototype._toggleInput).toHaveBeenCalledWith(false);
  });

  test('for a preset `empty` operator toggles the list OFF and the input ON (keeps the existing operator)', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';

    filter.render();

    expect(filter._selectedOperator).toBe('empty'); // not overwritten by emptyValue.type
    expect((ChoiceFilter as any).prototype._toggleListSelection).toHaveBeenCalledWith(false);
    expect((ChoiceFilter as any).prototype._toggleInput).toHaveBeenCalledWith(true);
  });
});

describe('_renderReact()', () => {
  test('mounts the criteria stub and forwards the operator/open/hint props', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';
    filter._criteriaOpen = true;

    filter._renderReact();

    const node = stub(filter)!;
    expect(node).not.toBeNull();
    expect(node.getAttribute('data-selected-operator')).toBe('empty');
    expect(node.getAttribute('data-is-open')).toBe('true');
    expect(node.getAttribute('data-criteria-hint')).toBe('the-hint');
    expect(node.getAttribute('data-operator-choices')).toBe(JSON.stringify({in: 'In', empty: 'Empty'}));
    expect(node.getAttribute('data-update-label')).toBe('pim_common.update'); // identity translator
    expect(node.getAttribute('data-operator-label')).toBe('pim_common.operator');
  });

  test('passes isOpen=false when the criteria are not open', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';
    filter._criteriaOpen = false;

    filter._renderReact();

    expect(stub(filter)!.getAttribute('data-is-open')).toBe('false');
  });
});

describe('_renderCriteria()', () => {
  test('is a no-op that returns this and never mounts a legacy popup', () => {
    const filter: any = new Bridge();

    const result = filter._renderCriteria();

    expect(result).toBe(filter);
    expect(filter.el.childElementCount).toBe(0); // nothing rendered
  });
});

describe('_updateCriteriaHint()', () => {
  test('re-renders React and returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._updateCriteriaHint();

    expect(spy).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter);
  });
});

describe('_showCriteria()', () => {
  test('opens the criteria, chains to the base show, re-renders, and returns this', () => {
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
  test('closes the criteria, chains to the base hide, re-renders, and returns this', () => {
    const filter: any = new Bridge();
    filter._criteriaOpen = true;
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._hideCriteria();

    expect(filter._criteriaOpen).toBe(false);
    expect((ChoiceFilter as any).prototype._hideCriteria).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(stub(filter)!.getAttribute('data-is-open')).toBe('false');
    expect(result).toBe(filter);
  });
});

describe('_updateCriteriaSelectorPosition()', () => {
  test('is a no-op that returns this without re-rendering React (positioning owned by the hook)', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._updateCriteriaSelectorPosition();

    expect(result).toBe(filter);
    expect(spy).not.toHaveBeenCalled();
  });
});

describe('_highlightDropdown()', () => {
  test('records the operator as React state, re-renders, and returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._highlightDropdown('empty');

    expect(filter._selectedOperator).toBe('empty');
    expect(spy).toHaveBeenCalledTimes(1);
    expect(stub(filter)!.getAttribute('data-selected-operator')).toBe('empty');
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

  test('reads the clicked operator, re-renders, toggles list ON / input OFF for `in`, and prevents default', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');
    const e = eventFor('in');

    filter._onSelectOperator(e);

    expect(filter._selectedOperator).toBe('in');
    expect(spy).toHaveBeenCalledTimes(1);
    expect((ChoiceFilter as any).prototype._toggleListSelection).toHaveBeenCalledWith(true);
    expect((ChoiceFilter as any).prototype._toggleInput).toHaveBeenCalledWith(false);
    expect(e.preventDefault).toHaveBeenCalledTimes(1);
  });

  test('toggles list OFF / input ON for the `empty` operator', () => {
    const filter: any = new Bridge();
    const e = eventFor('empty');

    filter._onSelectOperator(e);

    expect(filter._selectedOperator).toBe('empty');
    expect((ChoiceFilter as any).prototype._toggleListSelection).toHaveBeenCalledWith(false);
    expect((ChoiceFilter as any).prototype._toggleInput).toHaveBeenCalledWith(true);
  });
});

describe('_readDOMValue()', () => {
  test('with emptyChoice reads the input value for a value-bearing operator', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';

    expect(filter._readDOMValue()).toEqual({value: 'typed-value', type: 'in'});
    expect((ChoiceFilter as any).prototype._getInputValue).toHaveBeenCalledWith('.value');
  });

  test('returns an empty value for the empty/not-empty operators', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';

    expect(filter._readDOMValue()).toEqual({value: '', type: 'empty'});
  });

  test('forces the `in` operator when emptyChoice is false, ignoring the selected operator', () => {
    const filter: any = new Bridge();
    filter.emptyChoice = false;
    filter._selectedOperator = 'empty';

    expect(filter._readDOMValue()).toEqual({value: 'typed-value', type: 'in'});
  });
});

describe('_onValueUpdated()', () => {
  test('records the operator, shows the value field for a value-bearing operator, and defers to TextFilter', () => {
    const filter: any = new Bridge();

    filter._onValueUpdated({type: 'in', value: 'x'});

    expect(filter._selectedOperator).toBe('in');
    expect(filter.$).toHaveBeenCalledWith('.value');
    expect(filter._$obj.show).toHaveBeenCalledTimes(1);
    expect(filter._$obj.hide).not.toHaveBeenCalled();
    expect((TextFilter as any).prototype._onValueUpdated).toHaveBeenCalledWith({type: 'in', value: 'x'});
  });

  test('hides the value field for the empty/not-empty operators', () => {
    const filter: any = new Bridge();

    filter._onValueUpdated({type: 'not empty', value: ''});

    expect(filter._selectedOperator).toBe('not empty');
    expect(filter._$obj.hide).toHaveBeenCalledTimes(1);
    expect(filter._$obj.show).not.toHaveBeenCalled();
    expect((TextFilter as any).prototype._onValueUpdated).toHaveBeenCalledTimes(1);
  });
});

describe('remove()', () => {
  test('unmounts the React tree and chains to AbstractFilter.remove, returning its result', () => {
    const filter: any = new Bridge();
    filter.render();
    expect(stub(filter)).not.toBeNull();

    const result = filter.remove();

    expect(stub(filter)).toBeNull(); // ReactDOM.unmountComponentAtNode emptied this.el
    expect((AbstractFilter as any).prototype.remove).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter); // returns the base remove()'s return value (this)
  });
});
