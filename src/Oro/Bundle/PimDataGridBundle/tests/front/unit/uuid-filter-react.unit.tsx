// Mock the React `choice-filter-react` base BEFORE importing the uuid bridge (jest.mock is hoisted).
// `oro/datafilter/choice-filter-react` is not resolvable in the Jest/Stryker sandbox — provide a
// minimal Backbone-style constructor with the `.extend` chaining the bridge relies on, an `el`, and
// a jest.fn `initialize` on the PROTOTYPE so the bridge's
// `ChoiceFilterReact.prototype.initialize.apply(this, arguments)` super-call resolves and can be
// asserted.
jest.mock(
  'oro/datafilter/choice-filter-react',
  () => {
    function ChoiceFilterReact(this: any) {
      this.el = document.createElement('div');
    }
    // Stub the base methods the bridge reaches through `super`.
    (ChoiceFilterReact as any).prototype.initialize = jest.fn();
    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class), not a
    // hardcoded base — preserving the base's prototype methods for the subclass' super-calls.
    function backboneExtend(this: any, proto: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, proto);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (ChoiceFilterReact as any).extend = backboneExtend;
    return ChoiceFilterReact;
  },
  {virtual: true}
);

// Identity translator so returned/label keys are predictable and mutant-sensitive.
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';
import Bridge from '../../../Resources/public/js/datafilter/filter/uuid-filter-react';

const IN_LIST = 'pim_datagrid.filters.common.in_list';

beforeEach(() => {
  jest.clearAllMocks();
});

describe('uuid-filter-react bridge', () => {
  test('inherits from the React choice-filter-react base (extend chain preserved)', () => {
    const filter = new Bridge();

    // The base constructor runs and gives the subclass its `el`.
    expect(filter.el).toBeInstanceOf(HTMLDivElement);
    // The subclass prototype chains off the base prototype.
    expect((ChoiceFilterReact as any).prototype.isPrototypeOf(filter)).toBe(true);
  });

  test('initialize() sets the single `in` choice and empty value, then chains to the base', () => {
    const filter = new Bridge();

    filter.initialize({foo: 'bar'});

    // Exact `choices` list (kills string/array/object mutants).
    expect(filter.choices).toEqual([{label: IN_LIST, value: 'in'}]);
    // Exact `emptyValue` (kills the `type`/`value` mutants).
    expect(filter.emptyValue).toEqual({type: 'in', value: ''});
  });

  test('initialize() forwards its arguments to the base initialize via super-call', () => {
    const filter = new Bridge();
    const options = {name: 'sku', enabled: true};

    filter.initialize(options);

    expect((ChoiceFilterReact as any).prototype.initialize).toHaveBeenCalledTimes(1);
    // `apply(this, arguments)` — the base receives the exact same options object, bound to `this`.
    expect((ChoiceFilterReact as any).prototype.initialize).toHaveBeenCalledWith(options);
    expect((ChoiceFilterReact as any).prototype.initialize.mock.instances[0]).toBe(filter);
  });

  test('initialize() sets the fields BEFORE calling the base (config available to super)', () => {
    const filter = new Bridge();
    let choicesAtSuperCall: unknown;
    let emptyValueAtSuperCall: unknown;
    (ChoiceFilterReact as any).prototype.initialize.mockImplementationOnce(function (this: any) {
      choicesAtSuperCall = this.choices;
      emptyValueAtSuperCall = this.emptyValue;
    });

    filter.initialize();

    expect(choicesAtSuperCall).toEqual([{label: IN_LIST, value: 'in'}]);
    expect(emptyValueAtSuperCall).toEqual({type: 'in', value: ''});
  });

  test('_getOperatorChoices() returns exactly the single `in` operator map', () => {
    const filter = new Bridge();

    // Exact object — kills key-rename, value-swap and empty-object mutants.
    expect(filter._getOperatorChoices()).toEqual({in: IN_LIST});
    expect(Object.keys(filter._getOperatorChoices())).toEqual(['in']);
  });
});
