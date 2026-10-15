import $ from 'jquery';
import _ from 'underscore';
import NumberFilter from 'oro/datafilter/number-filter';
import TreeView from 'pim/tree/view';
import mediator from 'oro/mediator';

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
   * Adopting the container does NOT start delegating the inherited `ChoiceFilter` events hash
   * (`keyup input`, `click .filter-criteria-selector`, ...) — which would have mattered, since the
   * tree renders an "include sub-categories" checkbox that `keyup input` would match. Both branches
   * of Backbone's `_ensureElement` call `setElement(element, false)`, and that `false` is the
   * delegate flag, so this is exactly as inert as the old manual `this.$el = $(container)` was.
   *
   * @param {Object}   options
   * @param {String|jQuery} options.el                The container to render the tree into.
   * @param {Object}   [options.urlParams]            FLAT grid params, bracket-notation keys, as
   *                                                  `datagrid:getParams` emits them. A nested object
   *                                                  is silently ignored — the keys read below would
   *                                                  not match.
   * @param {String}   options.gridName               Grid alias, used to build those keys.
   * @param {String}   options.categoryBaseRoute      Route prefix for `_listtree` / `_children`.
   * @param {Function} [options.updateCallback]       Called once with the initial value.
   */
  initialize: function (options) {
    options = options || {};
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

    const onTreeUpdated = (treeLabel, categoryLabel) => {
      this.trigger('update_label', treeLabel, categoryLabel);
    };

    this.$el[0].addEventListener('tree.updated', event => {
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

    this.listenTo(mediator, 'datagrid_filters:build.post', function (filtersManager) {
      this.listenTo(filtersManager, 'collection-filters:createState.post', function (filtersState) {
        _.extend(filtersState, {category: this._getTreeState()});
      });
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
  _getTreeState: function () {
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

  getValue: function () {
    if (!this.$el.is(':visible')) {
      return this.emptyValue;
    }

    return NumberFilter.prototype.getValue.apply(this, arguments);
  },

  /**
   * Get initial state for the tree
   */
  _getInitialState: function () {
    return {
      selectedNode: +this.value.value.categoryId,
      selectedTree: +this.value.value.treeId,
      includeSub: !!this.value.type,
    };
  },

  /**
   * Sync the tree state with the filter value
   */
  _updateState: function () {
    this.value = this._getTreeState();
  },

  /**
   * On tree updated
   */
  _onTreeUpdated: function () {
    if (!_.isEqual(this.value, this._getTreeState())) {
      this._updateState();
      this._triggerUpdate();
    }
  },

  /**
   * @inheritDoc
   */
  _triggerUpdate: function () {
    this.trigger('update', this.value);
  },

  /**
   * @inheritDoc
   */
  isEmpty: function () {
    return _.isEqual(this.emptyValue, this._getTreeState());
  },

  /**
   * @inheritDoc
   */
  reset: function () {
    NumberFilter.prototype.reset.apply(this, arguments);
  },
});
