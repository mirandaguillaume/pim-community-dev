import _ from 'underscore';
import AbstractAction from 'oro/datagrid/abstract-action';
import DatagridState from 'pim/datagrid/state';

export default AbstractAction.extend({
  /** @property oro.PageableCollection */
  collection: undefined,

  /**
   * Initialize action
   *
   * @param {Object} options
   * @param {oro.PageableCollection} options.collection Collection
   * @throws {TypeError} If collection is undefined
   */
  initialize: function (options) {
    options = options || {};

    if (!options.datagrid) {
      throw new TypeError("'datagrid' is required");
    }
    this.collection = options.datagrid.collection;
    this.datagrid = options.datagrid;

    AbstractAction.prototype.initialize.apply(this, arguments);
  },

  /**
   * Execute reset collection
   */
  execute: function () {
    var initialState = this.collection.initialState;
    var datagridState = DatagridState.get(this.datagrid.name, ['initialViewState']);
    var view = initialState.parameters.view;

    if (_.has(initialState, 'filters')) {
      initialState.filters = _.omit(initialState.filters, 'scope');
    }

    if (view && view.id !== '0' && datagridState.initialViewState.length !== 0) {
      initialState = this.collection.decodeStateData(datagridState.initialViewState);
    }

    this.collection.updateState(initialState);
    this.collection.fetch();
  },
});
