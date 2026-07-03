// Unit test for the `parent` React datagrid filter bridge (C1 Wave 4, Slice C5).
//
// The bridge (`parent-filter-react.ts`) is a thin subclass of the React `ChoiceFilterReact` base:
// it re-adds the parent filter's own choices/operators and the Select2 open behaviour, and defers
// everything else to the base via `ChoiceFilterReact.prototype.<method>.apply(this, ...)` super
// calls. To exercise the overrides in isolation we mock the WHOLE base (unresolvable in the Jest /
// Stryker sandbox — it pulls ReactDOM + the DSM criteria component) with a Backbone-style
// constructor whose prototype carries jest.fn() stubs for every method the bridge reaches through,
// and whose `.extend(proto)` chains the subclass prototype off the CALLING class (so the super
// calls and inheritance are preserved). The mocks are hoisted above the bridge import.

// The legacy React choice-filter base is not resolvable in the Jest/Stryker sandbox — virtual mock.
// The bridge reaches the base via `ChoiceFilterReact.prototype.initialize/_showCriteria` (super
// calls) and inherits `_readDOMValue`, `_enableListSelection`, `_disableListSelection`. Each is a
// jest.fn() stub so we can assert the delegation. The constructor gives the instance an `el` and a
// chainable `$` (Backbone's scoped jQuery) so `_focusCriteria` has something to drive.
jest.mock(
  'oro/datafilter/choice-filter-react',
  () => {
    function makeChain() {
      const chain: any = {};
      chain.focus = jest.fn(() => chain);
      chain.select = jest.fn(() => chain);
      return chain;
    }

    function ChoiceFilterReact(this: any) {
      this.el = document.createElement('div');
      this.$ = jest.fn(() => makeChain());
    }

    const proto: any = ChoiceFilterReact.prototype;
    proto.initialize = jest.fn();
    proto._showCriteria = jest.fn();
    proto._hideCriteria = jest.fn();
    proto._renderReact = jest.fn();
    proto.render = jest.fn();
    proto.remove = jest.fn(function (this: any) {
      return this;
    });
    // `_readDOMValue` is the source of truth read by the bridge's `_showCriteria`; default to the
    // `in` operator, individual tests override it to drive the enable/disable branch.
    proto._readDOMValue = jest.fn(() => ({type: 'in', value: ''}));
    proto._enableListSelection = jest.fn();
    proto._disableListSelection = jest.fn();

    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class), NOT a
    // hardcoded base — otherwise the bridge would lose the base's own methods and the super calls
    // would resolve to the wrong prototype.
    function backboneExtend(this: any, protoProps: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, protoProps);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (ChoiceFilterReact as any).extend = backboneExtend;

    return ChoiceFilterReact;
  },
  {virtual: true}
);

// `oro/translator` — identity so operator/choice keys are predictable in the assertions.
jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';
import ParentFilterReact from '../../../Resources/public/js/datafilter/filter/parent-filter-react';

const baseProto: any = (ChoiceFilterReact as any).prototype;

describe('parent-filter-react (parent React filter bridge)', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    // clearAllMocks wipes call data but keeps the factory implementation; restore the default
    // `_readDOMValue` return in case a test replaced it with mockReturnValue.
    baseProto._readDOMValue.mockReturnValue({type: 'in', value: ''});
  });

  describe('initialize', () => {
    test('sets the parent choices + emptyValue and chains to the base initialize', () => {
      const filter: any = new (ParentFilterReact as any)();

      filter.initialize('opt-a', 'opt-b');

      expect(filter.choices).toEqual([
        {label: 'pim_datagrid.filters.common.in_list', value: 'in'},
        {label: 'pim_datagrid.filters.common.empty', value: 'empty'},
      ]);
      expect(filter.emptyValue).toEqual({type: 'in', value: ''});
      // super called, forwarding the same arguments.
      expect(baseProto.initialize).toHaveBeenCalledTimes(1);
      expect(baseProto.initialize).toHaveBeenCalledWith('opt-a', 'opt-b');
    });
  });

  describe('_getOperatorChoices', () => {
    test('returns exactly the in/empty operator map (translated keys)', () => {
      const filter: any = new (ParentFilterReact as any)();

      expect(filter._getOperatorChoices()).toEqual({
        in: 'pim_datagrid.filters.common.in_list',
        empty: 'pim_datagrid.filters.common.empty',
      });
    });
  });

  describe('_showCriteria', () => {
    test('chains to the base, enables list selection for the "in" operator, and focuses', () => {
      const filter: any = new (ParentFilterReact as any)();
      baseProto._readDOMValue.mockReturnValue({type: 'in', value: ''});
      const focusSpy = jest.spyOn(filter, '_focusCriteria').mockImplementation(() => undefined);

      filter._showCriteria();

      expect(baseProto._showCriteria).toHaveBeenCalledTimes(1);
      expect(baseProto._enableListSelection).toHaveBeenCalledTimes(1);
      expect(baseProto._disableListSelection).not.toHaveBeenCalled();
      expect(focusSpy).toHaveBeenCalledTimes(1);
    });

    test('disables list selection for a non-"in" operator (empty)', () => {
      const filter: any = new (ParentFilterReact as any)();
      baseProto._readDOMValue.mockReturnValue({type: 'empty', value: ''});
      jest.spyOn(filter, '_focusCriteria').mockImplementation(() => undefined);

      filter._showCriteria();

      expect(baseProto._showCriteria).toHaveBeenCalledTimes(1);
      expect(baseProto._disableListSelection).toHaveBeenCalledTimes(1);
      expect(baseProto._enableListSelection).not.toHaveBeenCalled();
    });
  });

  describe('_focusCriteria', () => {
    test('focuses and selects the Select2-internal input under the criteria selector', () => {
      const filter: any = new (ParentFilterReact as any)();
      filter.criteriaSelector = '.filter-criteria';
      const chain: any = {};
      chain.focus = jest.fn(() => chain);
      chain.select = jest.fn(() => chain);
      filter.$ = jest.fn(() => chain);

      filter._focusCriteria();

      // The exact composed selector (kills the string-concat mutant).
      expect(filter.$).toHaveBeenCalledWith('.filter-criteria input.select2-input');
      expect(chain.focus).toHaveBeenCalledTimes(1);
      expect(chain.select).toHaveBeenCalledTimes(1);
    });
  });
});
