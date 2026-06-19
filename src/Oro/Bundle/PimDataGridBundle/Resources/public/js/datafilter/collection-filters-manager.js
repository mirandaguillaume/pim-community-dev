import _ from 'underscore';
import FiltersManager from 'oro/datafilter/filters-manager';
import createGridStateFilterWriter from '../datagrid/createGridStateFilterWriter';

/**
 * @typedef {import('../datagrid/GridState').GridState} GridState
 * @typedef {import('../datagrid/GridState').FilterValues} FilterValues
 */

export default FiltersManager.extend({
  /**
   * Initialize filter list options
   *
   * @param {Object} options
   * @param {{state: GridState, fetch: function, on: function}} [options.collection]
   * @param {Object} [options.filters]
   * @param {String} [options.addButtonHint]
   */
  initialize: function (options) {
    this.collection = options.collection;
    this.stateWriter = createGridStateFilterWriter(this.collection);

    this.collection.on('beforeFetch', this._beforeCollectionFetch, this);
    this.collection.on('updateState', this._onUpdateCollectionState, this);
    this.collection.on('reset', this._onCollectionReset, this);

    FiltersManager.prototype.initialize.apply(this, arguments);
  },

  /**
   * Triggers when filter is updated
   *
   * @param {oro.filter.AbstractFilter} filter
   * @protected
   */
  _onFilterUpdated: function (filter) {
    if (this.ignoreFiltersUpdateEvents) {
      return;
    }
    this.stateWriter.resetPage();
    this.collection.fetch();

    FiltersManager.prototype._onFilterUpdated.apply(this, arguments);
  },

  /**
   * Triggers before collection fetch it's data. Writes the active filter values into grid state
   * through the explicit GridStateFilterWriter (Wave 4) instead of mutating `collection.state`.
   *
   * @protected
   */
  _beforeCollectionFetch: function () {
    this.stateWriter.setFilters(this._createState());
  },

  /**
   * Triggers when collection state is updated
   *
   * @param {{state: GridState}} collection
   */
  _onUpdateCollectionState: function (collection) {
    this.ignoreFiltersUpdateEvents = true;
    this._applyState(collection.state.filters || {});
    this.ignoreFiltersUpdateEvents = false;
  },

  /**
   * Triggers after collection resets it's data
   *
   * @param {{state: GridState, $el: Object}} collection
   * @protected
   */
  _onCollectionReset: function (collection) {
    if (collection.state.totalRecords > 0 && this.$el.children().length > 0) {
      this.$el.show();
    }
  },

  /**
   * Create state according to filters parameters
   *
   * @return {FilterValues}
   * @protected
   */
  _createState: function () {
    /** @type {FilterValues} */
    var state = {};
    _.each(
      this.filters,
      function (filter, name) {
        var shortName = '__' + name;
        if (filter.enabled) {
          if (!filter.isEmpty()) {
            state[name] = filter.getValue();
          } else if (!filter.defaultEnabled) {
            state[shortName] = 1;
          }
        } else if (filter.defaultEnabled) {
          state[shortName] = 0;
        }
      },
      this
    );

    this.trigger('collection-filters:createState.post', state);

    return state;
  },

  /**
   * Apply filter values from state
   *
   * @param {FilterValues} state
   * @protected
   * @return {*}
   */
  _applyState: function (state) {
    var toEnable = [],
      toDisable = [];

    _.each(
      this.filters,
      function (filter, name) {
        var shortName = '__' + name,
          filterState;
        if (_.has(state, name) && 0 !== _.size(state)) {
          filterState = state[name];
          if (!_.isObject(filterState)) {
            filterState = {
              value: filterState,
            };
          }
          filter.setValue(filterState);
          toEnable.push(filter);
        } else if (_.has(state, shortName)) {
          filter.reset();
          if (Number(state[shortName])) {
            toEnable.push(filter);
          } else {
            toDisable.push(filter);
          }
        } else {
          filter.reset();
          if (filter.defaultEnabled) {
            toEnable.push(filter);
          } else {
            toDisable.push(filter);
          }
        }
      },
      this
    );

    this.enableFilters(toEnable);
    this.disableFilters(toDisable);

    return this;
  },
});
