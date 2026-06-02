function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Backbone = __pimInterop(require('backbone'));
var Backgrid = __pimInterop(require('backgrid'));
var HeaderCell = __pimInterop(require('oro/datagrid/header-cell'));
('use strict');

module.exports = Backgrid.Header.extend({
  /** @property */
  tagName: 'thead',

  /** @property */
  row: Backgrid.HeaderRow,

  /** @property */
  headerCell: HeaderCell,

  /**
   * @inheritDoc
   */
  initialize: function (options) {
    if (!options.collection) {
      throw new TypeError("'collection' is required");
    }
    if (!options.columns) {
      throw new TypeError("'columns' is required");
    }

    this.columns = options.columns;
    if (!(this.columns instanceof Backbone.Collection)) {
      this.columns = new Backgrid.Columns(this.columns);
    }

    this.row = new this.row({
      columns: this.columns,
      collection: this.collection,
      headerCell: this.headerCell,
    });
  },
});
