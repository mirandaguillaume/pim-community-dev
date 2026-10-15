import $ from 'jquery';
import _ from 'underscore';
import NumberFilter from 'oro/datafilter/number-filter';
import TreeView from 'pim/tree/view';
import mediator from 'oro/mediator';

/**
 * The filter value, in the shape the datagrid's `_filter[category]` query parameter expects.
 * `type` is the "include sub-categories" flag, carried as a number because that is what the
 * backend number-filter contract uses (1 = include, 0 = exclude).
 */
export type CategoryFilterValue = {
  value: {
    treeId: number;
    categoryId: number;
  };
  type: number;
};

/**
 * What the tree itself is driven by. `includeSub` is the same flag as `CategoryFilterValue.type`,
 * in its boolean form — `_getInitialState` and `_getTreeState` convert between the two.
 */
export type CategoryTreeState = {
  selectedNode: number;
  selectedTree: number;
  includeSub: boolean;
};

export type CategoryFilterOptions = {
  /** The container to render the tree into — a selector string or a jQuery object. */
  el: string | JQuery;
  /** Grid alias, used to build the `urlParams` keys read below. */
  gridName: string;
  /** Route prefix for `_listtree` / `_children`. */
  categoryBaseRoute: string;
  /**
   * FLAT grid params, bracket-notation keys, as `datagrid:getParams` emits them. A nested object
   * is silently ignored — the keys read below would not match.
   */
  urlParams?: Record<string, string>;
  /** Called once, synchronously, with the initial value. */
  updateCallback?: (value: CategoryFilterValue) => void;
};

export default NumberFilter.extend({
  /**
   * @inheritDoc
   */
  value: {},

  treeView: null,

  /**
   * Built directly by its two consumers (`pim/product/grid/category-tree` and `pim/common/item-picker`)
   * rather than resolved through `FilterTypeRegistry`, because it is not a grid-declared filter: it
   * renders a category tree into a container the caller owns.
   *
   * That construction used to be five positional arguments, which fought Backbone in two ways.
   * Backbone hands the FIRST constructor argument to `_configure()` as the view's options and runs
   * `_ensureElement()` before `initialize()`, so passing `urlParams` first meant a bag of grid query
   * parameters landed in the options slot (harmless only because none of its keys are in Backbone's
   * `viewOptions`), and passing the container fourth meant Backbone built a `<div>` that this method
   * immediately threw away with `this.$el.remove()`.
   *
   * Named options fix both: `el` is one of Backbone's own `viewOptions`, so `_ensureElement()` adopts
   * the caller's container — a selector string or a jQuery object, `setElement` accepts either — and
   * there is no element to discard. None of the names below collide with what `abstract-filter`
   * reads out of its options (`enabled`, `canDisable`, `placeholder`, `showLabel`, `name`, `type`),
   * so the super-call behaves exactly as it did when it received `urlParams`.
   *
   * ONE THING TO KNOW about adopting the container: the inherited `ChoiceFilter` events hash IS
   * delegated onto it. `_ensureElement` passes `setElement(element, false)`, so it does not delegate
   * itself, but `Backbone.View`'s constructor calls `this.delegateEvents()` unconditionally right
   * after `initialize()` — so the flag only defers the delegation, it does not prevent it. This is
   * not a regression introduced by the named options: the old code reassigned `this.$el` to the same
   * container inside `initialize`, before that same trailing `delegateEvents()` ran, so the hash was
   * delegated onto the container then too. It stays inert for a reason that has nothing to do with
   * either shape: the only listener that can match inside a category tree is `keyup input` (the
   * "include sub-categories" checkbox), its handler `_onReadCriteriaInputKey` acts on Enter alone,
   * and the `input[name="value"]` it would then read does not exist in this container.
   * `product_category-filter.unit.ts` pins all of that.
   *
   * @param {Object} options
   */
  initialize: function (options: CategoryFilterOptions) {
    options = options || ({} as CategoryFilterOptions);
    const {urlParams, gridName, categoryBaseRoute, updateCallback} = options;

    this.emptyValue = {
      value: {
        treeId: 0,
        categoryId: -2,
      },
      type: 1,
    };

    this.value = $.extend(true, {}, this.emptyValue);
    NumberFilter.prototype.initialize.call(this, options);

    if (urlParams && urlParams[gridName + '[_filter][category][value][treeId]']) {
      this.value.value.treeId = parseInt(urlParams[gridName + '[_filter][category][value][treeId]']);
    }
    if (urlParams && urlParams[gridName + '[_filter][category][value][categoryId]']) {
      this.value.value.categoryId = parseInt(urlParams[gridName + '[_filter][category][value][categoryId]']);
    }

    const onTreeUpdated = (treeLabel: string, categoryLabel: string) => {
      this.trigger('update_label', treeLabel, categoryLabel);
    };

    this.$el[0].addEventListener('tree.updated', (event: Event) => {
      this._onTreeUpdated();
      event.preventDefault();
    });

    this.treeView = new TreeView(
      this.$el[0],
      this._getInitialState(),
      {
        listTree: `${categoryBaseRoute}_listtree`,
        children: `${categoryBaseRoute}_children`,
      },
      onTreeUpdated
    );

    this.listenTo(mediator, 'datagrid_filters:build.post', function (this: any, filtersManager: any) {
      this.listenTo(
        filtersManager,
        'collection-filters:createState.post',
        function (this: any, filtersState: Record<string, CategoryFilterValue>) {
          _.extend(filtersState, {category: this._getTreeState()});
        }
      );
      filtersManager.listenTo(this, 'update', filtersManager._onFilterUpdated);
    });

    this.listenTo(mediator, 'filters-column:init', () => {
      mediator.trigger('filters-column:update-filter', {category: this._getTreeState()}, true);

      this.listenTo(this, 'update', () => {
        mediator.trigger('filters-column:update-filter', {category: this._getTreeState()});
      });
    });

    mediator.on('grid_action_execute:product-grid:delete', () => {
      this.treeView.refresh();
    });

    if (undefined !== updateCallback) {
      updateCallback(this.value);
    }
  },

  /**
   * Get the current tree state
   */
  _getTreeState: function (): CategoryFilterValue {
    if (!this.$el.is(':visible')) {
      return this.emptyValue;
    }

    var state = this.treeView.getState();

    return {
      value: {
        treeId: state.selectedTree,
        categoryId: state.selectedNode,
      },
      type: +state.includeSub,
    };
  },

  getValue: function (): CategoryFilterValue {
    if (!this.$el.is(':visible')) {
      return this.emptyValue;
    }

    return NumberFilter.prototype.getValue.apply(this, arguments);
  },

  /**
   * Get initial state for the tree
   */
  _getInitialState: function (): CategoryTreeState {
    return {
      selectedNode: +this.value.value.categoryId,
      selectedTree: +this.value.value.treeId,
      includeSub: !!this.value.type,
    };
  },

  /**
   * Sync the tree state with the filter value
   */
  _updateState: function (): void {
    this.value = this._getTreeState();
  },

  /**
   * On tree updated
   */
  _onTreeUpdated: function (): void {
    if (!_.isEqual(this.value, this._getTreeState())) {
      this._updateState();
      this._triggerUpdate();
    }
  },

  /**
   * @inheritDoc
   */
  _triggerUpdate: function (): void {
    this.trigger('update', this.value);
  },

  /**
   * @inheritDoc
   */
  isEmpty: function (): boolean {
    return _.isEqual(this.emptyValue, this._getTreeState());
  },

  /**
   * @inheritDoc
   */
  reset: function (): void {
    NumberFilter.prototype.reset.apply(this, arguments);
  },
});
