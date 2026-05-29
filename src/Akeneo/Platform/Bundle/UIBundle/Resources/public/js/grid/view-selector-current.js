'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
require('backbone');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/grid/view-selector/current'));

module.exports = BaseForm.extend({
  template: _.template(template),
  datagridView: null,
  dirtyColumns: false,
  dirtyFilters: false,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'grid:view-selector:state-changed', this.onDatagridStateChange.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        view: this.datagridView,
        dirtyFilters: this.dirtyFilters,
        dirtyColumns: this.dirtyColumns,
      })
    );

    this.renderExtensions();

    return this;
  },

  /**
   * Method called on datagrid state change (when columns or filters are modified).
   * Set the state to dirty if it's the case then re-render this extension.
   *
   * @param {Object} datagridState
   */
  onDatagridStateChange: function (datagridState) {
    if (null === datagridState.columns) {
      datagridState.columns = '';
    }

    var initialView = this.getRoot().initialView;
    var initialViewExists = null !== initialView && 0 !== initialView.id;

    var filtersModified = this.areFiltersModified(initialView.filters, datagridState.filters);
    var columnsModified = !_.isEqual(initialView.columns, datagridState.columns.split(','));

    if (initialViewExists) {
      this.dirtyFilters = filtersModified;
      this.dirtyColumns = columnsModified;
    } else {
      var isDefaultFilters = '' === datagridState.filters;
      var isDefaultColumns = _.isEqual(this.getRoot().defaultColumns, datagridState.columns.split(','));

      this.dirtyFilters = !isDefaultFilters;
      this.dirtyColumns = !isDefaultColumns;
    }

    this.render();
  },

  /**
   * Set the view of this module.
   *
   * @param {Object} view
   */
  setView: function (view) {
    this.datagridView = view;
  },

  /**
   * Check if current datagrid state filters are modified regarding the initial view
   *
   * @param {Object} initialViewFilters
   * @param {Object} datagridStateFilters
   *
   * @return {boolean}
   */
  areFiltersModified: function (initialViewFilters, datagridStateFilters) {
    return initialViewFilters !== datagridStateFilters;
  },
});
