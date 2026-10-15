// `oro/datafilter/number-filter`, `pim/tree/view` and `oro/mediator` are requirejs aliases that do
// not resolve in the Jest/Stryker sandbox, so all three are virtual-mocked below (jest.mock is
// hoisted above the imports).
//
// The number-filter mock is a REAL `Backbone.View` subclass, not the hand-rolled `backboneExtend`
// stub the sibling filter tests use. Those tests exercise pure logic (a NaN guard) where Backbone is
// irrelevant; here Backbone IS the subject. `product_category-filter` is the one filter built
// directly by its consumers, through named options, and what has to hold is Backbone's own
// `_configure` -> `_ensureElement` -> `initialize` -> `delegateEvents` sequence. A stub would only
// prove the stub.
jest.mock(
  'oro/datafilter/number-filter',
  () => {
    const Backbone = require('backbone');
    const $ = require('jquery');

    // Under webpack, backbone.js is piped through `imports-loader` with `wrapper: 'window'`
    // (webpack.config.js), so its internal `var root = this` IS window and `Backbone.$ = root.jQuery`
    // picks up the jQuery that `expose-loader` published there. Jest runs neither loader: `root` is
    // the CommonJS `module.exports`, `root.jQuery` is undefined, and `Backbone.$` comes out
    // undefined — `setElement` would then die on `element instanceof Backbone.$`. Bind it by hand.
    Backbone.$ = $;

    // One mock stands for the whole real ancestry: NumberFilter -> ChoiceFilter -> TextFilter ->
    // AbstractFilter -> Backbone.View. The events hash is ChoiceFilter's, verbatim, and the six
    // handlers are stubbed because Backbone 0.9.10's `delegateEvents` THROWS
    // (`Method "x" does not exist`) on a hash entry whose method is missing — in production they
    // come from TextFilter. Omitting them would make construction throw for a reason production
    // never has.
    return Backbone.View.extend({
      events: {
        'keyup input': '_onReadCriteriaInputKey',
        'keydown [type="text"]': '_preventEnterProcessing',
        'click .filter-update': '_onClickUpdateCriteria',
        'click .filter-criteria-selector': '_onClickCriteriaSelector',
        'click .AknDropdown-menuLink': '_onSelectOperator',
        'click .disable-filter': '_onClickDisableFilter',
      },
      initialize: jest.fn(),
      getValue: jest.fn(),
      reset: jest.fn(),
      _onReadCriteriaInputKey: jest.fn(),
      _preventEnterProcessing: jest.fn(),
      _onClickUpdateCriteria: jest.fn(),
      _onClickCriteriaSelector: jest.fn(),
      _onSelectOperator: jest.fn(),
      _onClickDisableFilter: jest.fn(),
    });
  },
  {virtual: true}
);

// TreeView records every construction so the tests can assert the DOM node, the initial state and
// the two routes it was handed. `nextState` is what `getState()` returns, settable per test.
jest.mock(
  'pim/tree/view',
  () => {
    function TreeView(this: any, domElement: any, initialState: any, routes: any, onChange: any) {
      this.args = {domElement, initialState, routes, onChange};
      this.getState = jest.fn(() => (TreeView as any).nextState);
      this.refresh = jest.fn();
      (TreeView as any).instances.push(this);
    }
    (TreeView as any).instances = [];
    (TreeView as any).nextState = {selectedTree: 0, selectedNode: -2, includeSub: true};
    return TreeView;
  },
  {virtual: true}
);

// A real Backbone.Events bus, so `listenTo(mediator, ...)` is genuinely wired rather than asserted
// against a stub.
jest.mock(
  'oro/mediator',
  () => {
    const Backbone = require('backbone');
    const _ = require('underscore');
    return _.extend({}, Backbone.Events);
  },
  {virtual: true}
);

import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';
import NumberFilter from 'oro/datafilter/number-filter';
import TreeView from 'pim/tree/view';
import mediator from 'oro/mediator';
import CategoryFilter from '../../../Resources/public/js/datafilter/filter/product_category-filter';

const Filter = CategoryFilter as any;
const Super = NumberFilter as any;
const Tree = TreeView as any;
const bus = mediator as any;

const GRID = 'product-grid';
const BASE_ROUTE = 'pim_enrich_categorytree';
const TREE_KEY = `${GRID}[_filter][category][value][treeId]`;
const CATEGORY_KEY = `${GRID}[_filter][category][value][categoryId]`;
const EMPTY_VALUE = {value: {treeId: 0, categoryId: -2}, type: 1};

let container: HTMLElement;

/**
 * jsdom reports every element as 0x0, so jQuery's `:visible` — `offsetWidth || offsetHeight ||
 * getClientRects().length` — is false for everything. The filter guards `_getTreeState()` and
 * `getValue()` on `:visible`, so both branches need to be reachable: give the container a non-zero
 * offsetHeight to make it visible.
 */
const setVisible = (element: HTMLElement, visible: boolean) =>
  Object.defineProperty(element, 'offsetHeight', {value: visible ? 24 : 0, configurable: true});

const build = (options: any = {}) =>
  new Filter({
    gridName: GRID,
    categoryBaseRoute: BASE_ROUTE,
    ...options,
    el: options.el === undefined ? $(container) : options.el,
  });

beforeEach(() => {
  jest.clearAllMocks();
  // `mediator` is module state shared by every test in the file, and the filter registers handlers
  // on it with both `listenTo` and `on`. Without this they accumulate across tests and one filter's
  // assertions start seeing another filter's triggers.
  bus.off();
  Tree.instances.length = 0;
  Tree.nextState = {selectedTree: 0, selectedNode: -2, includeSub: true};

  document.body.innerHTML = '<div id="category-tree"><input type="checkbox" id="include-sub"></div>';
  container = document.getElementById('category-tree') as HTMLElement;
  setVisible(container, false);
});

describe('construction: the named-options contract', () => {
  test('adopts the container passed as options.el (jQuery object) as both el and $el', () => {
    const filter = build();

    expect(filter.el).toBe(container);
    expect(filter.$el[0]).toBe(container);
  });

  test('adopts a container passed as a selector string', () => {
    const filter = build({el: '#category-tree'});

    expect(filter.el).toBe(container);
  });

  test('creates no throwaway element — the DOM is left exactly as the caller built it', () => {
    build();

    expect(document.body.children).toHaveLength(1);
    expect(document.body.firstElementChild).toBe(container);
  });

  test('forwards the whole options object to the super initialize', () => {
    const updateCallback = jest.fn();
    const urlParams = {[TREE_KEY]: '3'};

    build({urlParams, updateCallback});

    expect(Super.prototype.initialize).toHaveBeenCalledTimes(1);
    expect(Super.prototype.initialize).toHaveBeenCalledWith(
      expect.objectContaining({gridName: GRID, categoryBaseRoute: BASE_ROUTE, urlParams, updateCallback})
    );
  });

  test('tolerates being constructed with no options at all', () => {
    expect(() => new Filter()).not.toThrow();
  });
});

describe('construction: the inherited events hash is delegated onto the container', () => {
  // This is the hazard #481 reasoned about and got half right. `_ensureElement` does call
  // `setElement(element, false)`, so it does not delegate — but `Backbone.View`'s constructor calls
  // `this.delegateEvents()` unconditionally right after `initialize()`, so the flag only defers the
  // delegation. It is not a regression of the named options (the old positional shape reassigned
  // `this.$el` to the same container inside `initialize`, before that same trailing call), and it is
  // inert in practice — but it is real, and these tests say so out loud.
  test('construction does not throw even though the hash names six inherited handlers', () => {
    expect(() => build()).not.toThrow();
  });

  test('a keyup on an input inside the tree reaches the inherited handler', () => {
    build();

    $('#include-sub').trigger('keyup');

    expect(Super.prototype._onReadCriteriaInputKey).toHaveBeenCalledTimes(1);
  });

  test('the delegation is scoped to the container, not the document', () => {
    build();
    const outside = document.createElement('input');
    document.body.appendChild(outside);

    $(outside).trigger('keyup');

    expect(Super.prototype._onReadCriteriaInputKey).not.toHaveBeenCalled();
  });
});

describe('urlParams parsing', () => {
  test('falls back to "all categories" when no urlParams are supplied', () => {
    const filter = build();

    expect(filter.value).toEqual(EMPTY_VALUE);
    expect(filter.emptyValue).toEqual(EMPTY_VALUE);
  });

  test('reads treeId and categoryId from the bracket-notation keys, as integers', () => {
    const filter = build({urlParams: {[TREE_KEY]: '7', [CATEGORY_KEY]: '42'}});

    expect(filter.value.value).toEqual({treeId: 7, categoryId: 42});
    expect(filter.value.type).toBe(1);
  });

  test('each key is read independently', () => {
    const onlyTree = build({urlParams: {[TREE_KEY]: '7'}});
    expect(onlyTree.value.value).toEqual({treeId: 7, categoryId: -2});

    const onlyCategory = build({urlParams: {[CATEGORY_KEY]: '42'}});
    expect(onlyCategory.value.value).toEqual({treeId: 0, categoryId: 42});
  });

  test("the keys are namespaced by gridName — another grid's params are ignored", () => {
    const filter = build({
      urlParams: {'other-grid[_filter][category][value][treeId]': '7'},
    });

    expect(filter.value.value).toEqual({treeId: 0, categoryId: -2});
  });

  test('a NESTED params object is ignored and the default survives', () => {
    // The shape `common/item-picker` used to pass. The flat lookups never matched, so the tree fell
    // back to the default — which happens to be the "all categories" the picker wanted. It worked by
    // coincidence, and #481 stopped passing it. This pins that a nested object cannot be read.
    const filter = build({
      urlParams: {alias: GRID, params: {_filter: {category: {value: {treeId: 7, categoryId: 42}}}}} as any,
    });

    expect(filter.value.value).toEqual({treeId: 0, categoryId: -2});
  });

  test('an empty-string param is falsy and keeps the default rather than parsing to NaN', () => {
    const filter = build({urlParams: {[TREE_KEY]: '', [CATEGORY_KEY]: ''}});

    expect(filter.value.value).toEqual({treeId: 0, categoryId: -2});
  });

  test('does not mutate the prototype value shared by every instance', () => {
    const first = build({urlParams: {[TREE_KEY]: '7'}});
    const second = build();

    expect(first.value.value.treeId).toBe(7);
    expect(second.value.value.treeId).toBe(0);
  });
});

describe('TreeView wiring', () => {
  test('is constructed once, with the container DOM node and the two derived routes', () => {
    build();

    expect(Tree.instances).toHaveLength(1);
    expect(Tree.instances[0].args.domElement).toBe(container);
    expect(Tree.instances[0].args.routes).toEqual({
      listTree: `${BASE_ROUTE}_listtree`,
      children: `${BASE_ROUTE}_children`,
    });
  });

  test('receives an initial state derived from the parsed value', () => {
    build({urlParams: {[TREE_KEY]: '7', [CATEGORY_KEY]: '42'}});

    expect(Tree.instances[0].args.initialState).toEqual({selectedTree: 7, selectedNode: 42, includeSub: true});
  });

  test('its onChange callback re-emits update_label with both labels', () => {
    const filter = build();
    const onLabel = jest.fn();
    filter.on('update_label', onLabel);

    Tree.instances[0].args.onChange('Master catalog', 'Shoes');

    expect(onLabel).toHaveBeenCalledWith('Master catalog', 'Shoes');
  });

  test('is refreshed when a product is deleted from the grid', () => {
    build();

    bus.trigger('grid_action_execute:product-grid:delete');

    expect(Tree.instances[0].refresh).toHaveBeenCalledTimes(1);
  });
});

describe('updateCallback', () => {
  test('is called exactly once, synchronously, with the initial value', () => {
    const updateCallback = jest.fn();

    const filter = build({urlParams: {[TREE_KEY]: '7'}, updateCallback});

    expect(updateCallback).toHaveBeenCalledTimes(1);
    expect(updateCallback).toHaveBeenCalledWith(filter.value);
    expect(updateCallback.mock.calls[0][0]).toEqual({value: {treeId: 7, categoryId: -2}, type: 1});
  });

  test('is optional — construction completes without one', () => {
    expect(() => build({updateCallback: undefined})).not.toThrow();
  });
});

describe('_getInitialState', () => {
  test('coerces ids to numbers and the include-sub flag to a boolean', () => {
    const filter = build();
    filter.value = {value: {treeId: '5', categoryId: '9'}, type: 0};

    expect(filter._getInitialState()).toEqual({selectedTree: 5, selectedNode: 9, includeSub: false});
  });

  test('a non-zero type is truthy include-sub', () => {
    const filter = build();
    filter.value = {value: {treeId: 1, categoryId: 2}, type: 1};

    expect(filter._getInitialState().includeSub).toBe(true);
  });
});

describe('_getTreeState and the :visible guard', () => {
  test('a hidden container reports the empty value and never consults the tree', () => {
    const filter = build();
    setVisible(container, false);

    expect(filter._getTreeState()).toEqual(EMPTY_VALUE);
    expect(Tree.instances[0].getState).not.toHaveBeenCalled();
  });

  test('a visible container maps the tree state, with include-sub as 1', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 2, selectedNode: 15, includeSub: true};

    expect(filter._getTreeState()).toEqual({value: {treeId: 2, categoryId: 15}, type: 1});
  });

  test('include-sub false becomes type 0, not false', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 2, selectedNode: 15, includeSub: false};

    expect(filter._getTreeState().type).toBe(0);
  });
});

describe('getValue', () => {
  test('short-circuits to the empty value while hidden, without calling super', () => {
    const filter = build();
    setVisible(container, false);

    expect(filter.getValue()).toEqual(EMPTY_VALUE);
    expect(Super.prototype.getValue).not.toHaveBeenCalled();
  });

  test('delegates to super once visible', () => {
    const filter = build();
    setVisible(container, true);
    Super.prototype.getValue.mockReturnValueOnce('from-super');

    expect(filter.getValue()).toBe('from-super');
    expect(Super.prototype.getValue).toHaveBeenCalledTimes(1);
  });
});

describe('isEmpty', () => {
  test('is true when the tree still sits on the empty value', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 0, selectedNode: -2, includeSub: true};

    expect(filter.isEmpty()).toBe(true);
  });

  test('is false as soon as a category is selected', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 1, selectedNode: 15, includeSub: true};

    expect(filter.isEmpty()).toBe(false);
  });
});

describe('the tree.updated DOM event', () => {
  const dispatchTreeUpdated = () => {
    const event = new Event('tree.updated', {cancelable: true});
    container.dispatchEvent(event);
    return event;
  };

  test('publishes the new state as an update when it changed', () => {
    const filter = build();
    const onUpdate = jest.fn();
    filter.on('update', onUpdate);
    setVisible(container, true);
    Tree.nextState = {selectedTree: 1, selectedNode: 15, includeSub: false};

    dispatchTreeUpdated();

    expect(onUpdate).toHaveBeenCalledTimes(1);
    expect(filter.value).toEqual({value: {treeId: 1, categoryId: 15}, type: 0});
    expect(onUpdate).toHaveBeenCalledWith(filter.value);
  });

  test('stays silent when the tree state is unchanged', () => {
    const filter = build();
    const onUpdate = jest.fn();
    filter.on('update', onUpdate);
    setVisible(container, true);
    Tree.nextState = {selectedTree: 0, selectedNode: -2, includeSub: true};
    filter.value = filter._getTreeState();

    dispatchTreeUpdated();

    expect(onUpdate).not.toHaveBeenCalled();
  });

  test('cancels the event so it does not bubble on as a default action', () => {
    build();

    expect(dispatchTreeUpdated().defaultPrevented).toBe(true);
  });
});

describe('mediator wiring: the filters manager', () => {
  const buildFiltersManager = () => _.extend({}, Backbone.Events, {_onFilterUpdated: jest.fn()}) as any;

  test('contributes its category state when the manager collects filter state', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 3, selectedNode: 21, includeSub: true};
    const filtersManager = buildFiltersManager();

    bus.trigger('datagrid_filters:build.post', filtersManager);
    const filtersState: any = {existing: 'kept'};
    filtersManager.trigger('collection-filters:createState.post', filtersState);

    expect(filtersState).toEqual({
      existing: 'kept',
      category: {value: {treeId: 3, categoryId: 21}, type: 1},
    });
    expect(filter.value).toEqual(EMPTY_VALUE);
  });

  test("lets the manager observe the filter's own update events", () => {
    const filter = build();
    const filtersManager = buildFiltersManager();

    bus.trigger('datagrid_filters:build.post', filtersManager);
    filter.trigger('update', filter.value);

    expect(filtersManager._onFilterUpdated).toHaveBeenCalledTimes(1);
  });
});

describe('mediator wiring: the filters column', () => {
  const updateFilterCalls = (spy: jest.SpyInstance) =>
    spy.mock.calls.filter(call => call[0] === 'filters-column:update-filter');

  test('pushes its state on init, flagged as the initial push', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 4, selectedNode: 33, includeSub: false};
    const trigger = jest.spyOn(bus, 'trigger');

    bus.trigger('filters-column:init');

    expect(updateFilterCalls(trigger)).toEqual([
      ['filters-column:update-filter', {category: {value: {treeId: 4, categoryId: 33}, type: 0}}, true],
    ]);
    expect(filter.value).toEqual(EMPTY_VALUE);
    trigger.mockRestore();
  });

  test('pushes again on every later update, without the initial flag', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 4, selectedNode: 33, includeSub: true};
    bus.trigger('filters-column:init');
    const trigger = jest.spyOn(bus, 'trigger');

    filter.trigger('update', filter.value);

    expect(updateFilterCalls(trigger)).toEqual([
      ['filters-column:update-filter', {category: {value: {treeId: 4, categoryId: 33}, type: 1}}],
    ]);
    trigger.mockRestore();
  });

  test('pushes nothing before init', () => {
    const filter = build();
    const trigger = jest.spyOn(bus, 'trigger');

    filter.trigger('update', filter.value);

    expect(updateFilterCalls(trigger)).toEqual([]);
    trigger.mockRestore();
  });
});

describe('_updateState and reset', () => {
  test('_updateState copies the tree state onto the filter value', () => {
    const filter = build();
    setVisible(container, true);
    Tree.nextState = {selectedTree: 6, selectedNode: 60, includeSub: true};

    filter._updateState();

    expect(filter.value).toEqual({value: {treeId: 6, categoryId: 60}, type: 1});
  });

  test('reset delegates to super', () => {
    const filter = build();

    filter.reset();

    expect(Super.prototype.reset).toHaveBeenCalledTimes(1);
  });
});
