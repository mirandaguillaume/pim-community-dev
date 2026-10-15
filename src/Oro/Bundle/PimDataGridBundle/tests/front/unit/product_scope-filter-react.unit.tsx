// Unit test for the `product_scope-filter-react` bridge. Mock each legacy AMD dep BEFORE importing it
// (jest.mock is hoisted); the base mock uses the `backboneExtend` technique so the bridge's
// `SelectFilterReact.prototype.initialize.apply(this)` super-call resolves.
//
// `styled-components`, `akeneo-design-system` and `@akeneo-pim-community/legacy-bridge` are left
// unmocked, as in the other bridge tests — the component itself is stubbed, so nothing renders through
// the DSM.

const mediatorOnce = jest.fn();
const mediatorBind = jest.fn();
const userContextStore: Record<string, string> = {catalogScope: 'ecommerce'};
const gridState: Record<string, string> = {};

jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter(this: any) {}
    (AbstractFilter as any).prototype.render = jest.fn();
    (AbstractFilter as any).prototype._onValueUpdated = jest.fn();
    return AbstractFilter;
  },
  {virtual: true}
);

// The BASE the bridge extends. The constructor seeds the fields the bridge reads; the prototype carries
// the inherited methods it calls. `render` delegates to `_renderReact` exactly as the real
// `select-filter-react` does, so `resetValue()`'s re-render is observable.
jest.mock(
  'oro/datafilter/select-filter-react',
  () => {
    function SelectFilterReact(this: any) {
      this.el = document.createElement('div');
      this.$el = {prependTo: jest.fn()};
      this.label = 'Channel';
      this.value = {value: 'ecommerce'};
    }

    const proto = (SelectFilterReact as any).prototype;
    proto.initialize = jest.fn();
    proto._reactChoices = jest.fn(() => [
      {value: 'ecommerce', label: 'Ecommerce'},
      {value: 'mobile', label: 'Mobile'},
    ]);
    proto._formatRawValue = jest.fn((value: unknown) => value);
    proto.setValue = jest.fn();
    proto._isNewValueUpdated = jest.fn(() => true);
    proto._updateDOMValue = jest.fn();
    proto.render = function (this: any) {
      this._renderReact();
      return this;
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
    (SelectFilterReact as any).extend = backboneExtend;

    return SelectFilterReact;
  },
  {virtual: true}
);

jest.mock('oro/mediator', () => ({__esModule: true, default: {once: mediatorOnce, bind: mediatorBind}}), {
  virtual: true,
});
jest.mock(
  'pim/user-context',
  () => ({
    __esModule: true,
    default: {
      get: (key: string) => userContextStore[key],
      set: (key: string, value: string) => {
        userContextStore[key] = value;
      },
    },
  }),
  {virtual: true}
);
jest.mock(
  'pim/datagrid/state',
  () => ({
    __esModule: true,
    default: {
      get: (_grid: string, key: string) => gridState[key],
      set: (_grid: string, key: string, value: string) => {
        gridState[key] = value;
      },
    },
  }),
  {virtual: true}
);
jest.mock('oro/app', () => ({__esModule: true, default: {deepClone: (v: unknown) => JSON.parse(JSON.stringify(v))}}), {
  virtual: true,
});
jest.mock('oro/translator', () => ({__esModule: true, default: (key: string) => key}), {virtual: true});

// Stub the view: reflect the forwarded props into data-attributes so `_renderReact` mutants die.
jest.mock('../../../Resources/public/js/datafilter/filter/ScopeFilterCriteria', () => {
  const React = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      React.createElement('div', {
        className: 'scope-stub',
        'data-label': String(props.label),
        'data-selected': String(props.selected),
        'data-choices': JSON.stringify(props.choices),
      }),
  };
});

import ProductScopeFilterReact from '../../../Resources/public/js/datafilter/filter/product_scope-filter-react';

const build = () => new (ProductScopeFilterReact as any)();

describe('product_scope-filter-react', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    userContextStore.catalogScope = 'ecommerce';
    delete gridState.scope;
  });

  test('seeds catalogScope from the UserContext and registers its mediator wiring once', () => {
    const filter = build();
    filter.initialize();

    expect(filter.catalogScope).toBe('ecommerce');
    expect(mediatorOnce).toHaveBeenCalledTimes(2);
    expect(mediatorOnce.mock.calls.map(call => call[0])).toEqual([
      'datagrid_filters:rendered',
      'datagrid_filters:rendered',
    ]);
    expect(mediatorBind).toHaveBeenCalledWith('grid_load:complete', expect.any(Function));
  });

  test('forwards the label, the choices and catalogScope as the selected scope', () => {
    const filter = build();
    filter.catalogScope = 'mobile';
    filter._renderReact();

    const stub = filter.el.querySelector('.scope-stub')!;
    expect(stub.getAttribute('data-label')).toBe('Channel');
    expect(stub.getAttribute('data-selected')).toBe('mobile');
    expect(JSON.parse(stub.getAttribute('data-choices')!)).toEqual([
      {value: 'ecommerce', label: 'Ecommerce'},
      {value: 'mobile', label: 'Mobile'},
    ]);
  });

  test('reads its value from state, not from an active DOM class', () => {
    const filter = build();
    filter.catalogScope = 'mobile';

    expect(filter._readDOMValue()).toEqual({value: 'mobile'});
  });

  test('writing an external value syncs the state and re-renders', () => {
    const filter = build();
    filter.catalogScope = 'ecommerce';

    expect(filter._writeDOMValue({value: 'mobile'})).toBe(filter);
    expect(filter.catalogScope).toBe('mobile');
    expect(filter.el.querySelector('.scope-stub')!.getAttribute('data-selected')).toBe('mobile');
  });

  test('picking a scope publishes it to the UserContext, the model and the grid state', () => {
    const filter = build();
    filter.catalogScope = 'ecommerce';

    filter._onScopeChange('mobile');

    expect(filter.catalogScope).toBe('mobile');
    expect(userContextStore.catalogScope).toBe('mobile');
    expect(filter.setValue).toHaveBeenCalledWith({value: 'mobile'});
    expect(gridState.scope).toBe('mobile');
    expect(filter.el.querySelector('.scope-stub')!.getAttribute('data-selected')).toBe('mobile');
  });

  test('resetValue adopts the persisted grid scope without pushing a value', () => {
    gridState.scope = 'mobile';
    const filter = build();
    filter.catalogScope = 'ecommerce';

    filter.resetValue();

    expect(filter.catalogScope).toBe('mobile');
    expect(userContextStore.catalogScope).toBe('mobile');
    expect(filter.setValue).not.toHaveBeenCalled();
    expect(filter.el.querySelector('.scope-stub')!.getAttribute('data-selected')).toBe('mobile');
  });

  test('resetValue falls back to the UserContext scope and pushes it when the grid has none', () => {
    const filter = build();
    filter.catalogScope = 'ecommerce';

    filter.resetValue();

    expect(filter.setValue).toHaveBeenCalledWith({value: 'ecommerce'});
    expect(userContextStore.catalogScope).toBe('ecommerce');
  });

  test('_onValueUpdated syncs the state and re-renders', () => {
    const filter = build();
    filter.catalogScope = 'ecommerce';

    expect(filter._onValueUpdated({value: 'mobile'}, {value: 'ecommerce'})).toBe(filter);
    expect(filter.catalogScope).toBe('mobile');
    expect(filter.el.querySelector('.scope-stub')!.getAttribute('data-selected')).toBe('mobile');
  });

  test('setValueSilent clones the value and updates the DOM without touching the grid state', () => {
    const filter = build();

    expect(filter.setValueSilent({value: 'mobile'})).toBe(filter);
    expect(filter.value).toEqual({value: 'mobile'});
    expect(filter._updateDOMValue).toHaveBeenCalled();
    expect(gridState.scope).toBeUndefined();
  });

  test('disable and hide stay no-ops returning the filter', () => {
    const filter = build();

    expect(filter.disable()).toBe(filter);
    expect(filter.hide()).toBe(filter);
  });
});
