// Unit test for the `identifier-filter-react` bridge — a pure config subclass of the React
// `choice-filter-react` base. It overrides ONLY `initialize` (declares the 7 identifier operators
// + emptyValue, then chains to the base) and `_getOperatorChoices` (returns the operator map).
//
// Both AMD deps the bridge imports are mocked BEFORE the import (jest.mock is hoisted):
//  - `oro/translator`               → identity fn, so translation keys are asserted verbatim.
//  - `oro/datafilter/choice-filter-react` → a Backbone-style constructor with the `.extend` chaining
//    the bridge relies on (`Base.extend({...})` at load time) and a `prototype.initialize` jest.fn so
//    the bridge's `ChoiceFilterReact.prototype.initialize.apply(this, arguments)` super-call is
//    observable. The mock base constructor gives every instance an `el` (like the real Backbone.View).

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

jest.mock(
  'oro/datafilter/choice-filter-react',
  () => {
    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class) so the
    // bridge inherits the base prototype (incl. the `initialize` jest.fn reached via super).
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
    function ChoiceFilterReact(this: any) {
      this.el = document.createElement('div');
    }
    // Super target of the bridge's `initialize`: a spy so the chain-up is assertable.
    (ChoiceFilterReact as any).prototype.initialize = jest.fn();
    (ChoiceFilterReact as any).extend = backboneExtend;
    return ChoiceFilterReact;
  },
  {virtual: true}
);

import Bridge from '../../../Resources/public/js/datafilter/filter/identifier-filter-react';
import ChoiceFilterReactBase from 'oro/datafilter/choice-filter-react';

const baseInitialize = (ChoiceFilterReactBase as any).prototype.initialize as jest.Mock;

describe('identifier-filter-react bridge', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('is a subclass of the React choice-filter base and inherits its `el`', () => {
    const filter = new (Bridge as any)();

    expect(filter).toBeInstanceOf(Bridge as any);
    // `el` comes from the mocked base constructor (Backbone.View parity).
    expect(filter.el).toBeInstanceOf(HTMLElement);
    // Construction alone must NOT run initialize (the base ctor mock doesn't call it).
    expect(baseInitialize).not.toHaveBeenCalled();
  });

  test('initialize declares the 7 identifier choices in order', () => {
    const filter = new (Bridge as any)();

    filter.initialize();

    expect(filter.choices).toEqual([
      {label: 'pim_datagrid.filters.common.contains', value: '1'},
      {label: 'pim_datagrid.filters.common.does_not_contain', value: '2'},
      {label: 'pim_datagrid.filters.common.equal', value: '3'},
      {label: 'pim_datagrid.filters.common.start_with', value: '4'},
      {label: 'pim_datagrid.filters.common.in_list', value: 'in'},
      {label: 'pim_datagrid.filters.common.empty', value: 'empty'},
      {label: 'pim_datagrid.filters.common.not_empty', value: 'not empty'},
    ]);
  });

  test('initialize sets the emptyValue to the in-list default', () => {
    const filter = new (Bridge as any)();

    filter.initialize();

    expect(filter.emptyValue).toEqual({type: 'in', value: ''});
  });

  test('initialize chains up to the base `initialize`, forwarding its arguments', () => {
    const filter = new (Bridge as any)();
    const options = {foo: 'bar'};

    filter.initialize(options);

    expect(baseInitialize).toHaveBeenCalledTimes(1);
    expect(baseInitialize).toHaveBeenCalledWith(options);
    // Super-call runs on the filter instance (fields already set before chaining).
    expect(baseInitialize.mock.instances[0]).toBe(filter);
  });

  test('_getOperatorChoices returns the exact identifier operator map', () => {
    const filter = new (Bridge as any)();

    expect(filter._getOperatorChoices()).toEqual({
      1: 'pim_datagrid.filters.common.contains',
      2: 'pim_datagrid.filters.common.does_not_contain',
      3: 'pim_datagrid.filters.common.equal',
      4: 'pim_datagrid.filters.common.start_with',
      in: 'pim_datagrid.filters.common.in_list',
      empty: 'pim_datagrid.filters.common.empty',
      'not empty': 'pim_datagrid.filters.common.not_empty',
    });
  });

  test('_getOperatorChoices is a pure read — it does not chain to the base initialize', () => {
    const filter = new (Bridge as any)();

    filter._getOperatorChoices();

    expect(baseInitialize).not.toHaveBeenCalled();
  });
});
