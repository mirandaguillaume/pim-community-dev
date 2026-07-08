// Unit test for the `select2-rest-choice-filter-react` bridge: `export default
// Select2RestChoiceFilter.extend({})`. Sibling of select2-choice-filter-react (identical overrides,
// different base). Mock each legacy AMD dep BEFORE importing the bridge (jest.mock is hoisted).

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

jest.mock(
  'oro/datafilter/text-filter',
  () => {
    function TextFilter(this: any) {}
    (TextFilter as any).prototype._showCriteria = jest.fn();
    (TextFilter as any).prototype._hideCriteria = jest.fn();
    (TextFilter as any).prototype._onValueUpdated = jest.fn();
    return TextFilter;
  },
  {virtual: true}
);

jest.mock(
  'oro/datafilter/select2-rest-choice-filter',
  () => {
    function Select2RestChoiceFilter(this: any) {
      this.el = document.createElement('div');
      this.emptyValue = {type: 'in', value: ''};
      this.showLabel = false;
      this.label = 'Color';
      this.canDisable = false;
      this.emptyChoice = true;
      this.placeholder = 'All';
      this.operatorChoices = {in: 'In list', empty: 'Is empty', 'not empty': 'Is not empty'};
      this.criteriaValueSelectors = {value: 'input[name="value"]'};
      this._value = {type: 'in', value: ['red']};

      const $el: any = {};
      $el.addClass = jest.fn(() => $el);
      $el.data = jest.fn(() => 'select2-instance');
      $el.select2 = jest.fn(() => $el);
      $el.val = jest.fn(() => $el);
      this._$el = $el;
      this.$ = jest.fn(() => $el);
    }

    const proto = (Select2RestChoiceFilter as any).prototype;
    proto._getSelect2Config = jest.fn(() => ({multiple: true}));
    proto._getDisplayValue = jest.fn(() => ({value: 'red, blue'}));
    proto._getInputValue = jest.fn(() => ['red', 'blue']);
    proto._disableInput = jest.fn();
    proto._enableInput = jest.fn();
    proto.getValue = function (this: any) {
      return this._value;
    };

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
    (Select2RestChoiceFilter as any).extend = backboneExtend;

    return Select2RestChoiceFilter;
  },
  {virtual: true}
);

jest.mock('pim/initselect2', () => ({__esModule: true, default: {init: jest.fn()}}), {virtual: true});
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

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
        'data-empty-choice': String(props.emptyChoice),
        'data-operator-choices': JSON.stringify(props.operatorChoices),
        'data-update-label': String(props.updateLabel),
        'data-operator-label': String(props.operatorLabel),
      }),
  };
});

import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import initSelect2 from 'pim/initselect2';
import Bridge from '../../../Resources/public/js/datafilter/filter/select2-rest-choice-filter-react';

const stub = (filter: any): HTMLElement | null => filter.el.querySelector('.criteria-stub');
const s2Proto = (): any => jest.requireMock('oro/datafilter/select2-rest-choice-filter').prototype;
const initSpy = (): any => jest.requireMock('pim/initselect2').default.init as any;

beforeEach(() => {
  jest.clearAllMocks();
});

describe('render()', () => {
  test('chains to AbstractFilter.render, defaults the operator, mounts React, inits Select2, and returns this', () => {
    const filter: any = new Bridge();

    const result = filter.render();

    expect((AbstractFilter as any).prototype.render).toHaveBeenCalledTimes(1);
    expect(filter._selectedOperator).toBe('in');
    expect(stub(filter)).not.toBeNull();
    expect(filter._$el.addClass).toHaveBeenCalledWith('AknTextField--select2');
    expect(initSpy()).toHaveBeenCalledWith(filter._$el, {multiple: true});
    expect(result).toBe(filter);
  });

  test('keeps a preset operator instead of overwriting it', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';

    filter.render();

    expect(filter._selectedOperator).toBe('empty');
  });
});

describe('_renderReact()', () => {
  test('mounts the stub and forwards operator/open/hint/emptyChoice/operatorChoices', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';
    filter._criteriaOpen = true;

    filter._renderReact();

    const node = stub(filter)!;
    expect(node.getAttribute('data-selected-operator')).toBe('in');
    expect(node.getAttribute('data-is-open')).toBe('true');
    expect(node.getAttribute('data-empty-choice')).toBe('true');
    expect(node.getAttribute('data-operator-choices')).toBe(
      JSON.stringify({in: 'In list', empty: 'Is empty', 'not empty': 'Is not empty'})
    );
    expect(node.getAttribute('data-update-label')).toBe('pim_common.update');
    expect(node.getAttribute('data-operator-label')).toBe('pim_common.operator');
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

describe('_showCriteria() / _hideCriteria()', () => {
  test('_showCriteria opens, chains to TextFilter show, re-renders, returns this', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._showCriteria();

    expect(filter._criteriaOpen).toBe(true);
    expect((TextFilter as any).prototype._showCriteria).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(stub(filter)!.getAttribute('data-is-open')).toBe('true');
    expect(result).toBe(filter);
  });

  test('_hideCriteria closes, chains to TextFilter hide, re-renders, returns this', () => {
    const filter: any = new Bridge();
    filter._criteriaOpen = true;
    const spy = jest.spyOn(filter, '_renderReact');

    const result = filter._hideCriteria();

    expect(filter._criteriaOpen).toBe(false);
    expect((TextFilter as any).prototype._hideCriteria).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter);
  });
});

describe('_updateCriteriaSelectorPosition()', () => {
  test('is a no-op that returns this without re-rendering', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');

    expect(filter._updateCriteriaSelectorPosition()).toBe(filter);
    expect(spy).not.toHaveBeenCalled();
  });
});

describe('_highlightDropdown()', () => {
  test('records the operator as React state, re-renders, returns this', () => {
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

  test('for a value-bearing operator records it, re-renders, ENABLES the input, prevents default', () => {
    const filter: any = new Bridge();
    const spy = jest.spyOn(filter, '_renderReact');
    const e = eventFor('in');

    filter._onSelectOperator(e);

    expect(filter._selectedOperator).toBe('in');
    expect(spy).toHaveBeenCalledTimes(1);
    expect(s2Proto()._enableInput).toHaveBeenCalledTimes(1);
    expect(s2Proto()._disableInput).not.toHaveBeenCalled();
    expect(e.preventDefault).toHaveBeenCalledTimes(1);
  });

  test('for the empty/not-empty operators DISABLES the input', () => {
    const filter: any = new Bridge();
    const e = eventFor('empty');

    filter._onSelectOperator(e);

    expect(filter._selectedOperator).toBe('empty');
    expect(s2Proto()._disableInput).toHaveBeenCalledTimes(1);
    expect(s2Proto()._enableInput).not.toHaveBeenCalled();
  });
});

describe('_readDOMValue()', () => {
  test('with emptyChoice reads the input value for a value-bearing operator', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';

    expect(filter._readDOMValue()).toEqual({value: ['red', 'blue'], type: 'in'});
    expect(s2Proto()._getInputValue).toHaveBeenCalledWith('input[name="value"]');
  });

  test('returns an empty object value for the empty/not-empty operators', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';

    expect(filter._readDOMValue()).toEqual({value: {}, type: 'empty'});
  });

  test('forces the `in` operator when emptyChoice is false', () => {
    const filter: any = new Bridge();
    filter.emptyChoice = false;
    filter._selectedOperator = 'empty';

    expect(filter._readDOMValue()).toEqual({value: ['red', 'blue'], type: 'in'});
  });
});

describe('_getCriteriaHint()', () => {
  test('returns the operator label for the empty operator (from _selectedOperator, not the DOM)', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'empty';

    expect(filter._getCriteriaHint()).toBe('Is empty');
  });

  test('returns the operator label when the value type is empty/not-empty', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';
    filter._value = {type: 'not empty', value: {}};

    expect(filter._getCriteriaHint()).toBe('Is not empty');
  });

  test('returns the quoted display value for a value-bearing filter', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';
    filter._value = {type: 'in', value: ['red']};

    expect(filter._getCriteriaHint()).toBe('"red, blue"');
  });

  test('returns the placeholder when the display value is empty', () => {
    const filter: any = new Bridge();
    filter._selectedOperator = 'in';
    s2Proto()._getDisplayValue.mockReturnValueOnce({value: ''});

    expect(filter._getCriteriaHint()).toBe('All');
  });
});

describe('_onValueUpdated()', () => {
  test('records the operator then defers to TextFilter', () => {
    const filter: any = new Bridge();

    filter._onValueUpdated({type: 'not empty', value: {}});

    expect(filter._selectedOperator).toBe('not empty');
    expect((TextFilter as any).prototype._onValueUpdated).toHaveBeenCalledTimes(1);
  });
});

describe('remove()', () => {
  test('destroys Select2, unmounts the React tree, chains to AbstractFilter.remove', () => {
    const filter: any = new Bridge();
    filter.render();
    expect(stub(filter)).not.toBeNull();

    const result = filter.remove();

    expect(filter._$el.select2).toHaveBeenCalledWith('destroy');
    expect(stub(filter)).toBeNull();
    expect((AbstractFilter as any).prototype.remove).toHaveBeenCalledTimes(1);
    expect(result).toBe(filter);
  });

  test('does not destroy Select2 when it was never initialised', () => {
    const filter: any = new Bridge();
    filter._$el.data = jest.fn(() => undefined);

    filter.remove();

    expect(filter._$el.select2).not.toHaveBeenCalledWith('destroy');
  });
});
