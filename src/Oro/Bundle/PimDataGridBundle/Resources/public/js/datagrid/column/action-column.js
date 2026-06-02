function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Backgrid = __pimInterop(require('backgrid'));
var ActionCell = __pimInterop(require('oro/datagrid/action-cell'));
('use strict');

module.exports = Backgrid.Column.extend({
  /** @property {Object} */
  defaults: _.extend({}, Backgrid.Column.prototype.defaults, {
    name: 'rowActions',
    label: '',
    editable: false,
    cell: ActionCell,
    headerCell: Backgrid.HeaderCell.extend({
      className: 'AknGrid-headerCell action-column',
    }),
    sortable: false,
    actions: [],
  }),

  /**
   * {@inheritDoc}
   */
  initialize: function (attrs) {
    attrs = attrs || {};
    if (!attrs.cell) {
      attrs.cell = this.defaults.cell;
    }
    if (!attrs.name) {
      attrs.name = this.defaults.name;
    }
    if (!attrs.actions || _.isEmpty(attrs.actions)) {
      this.set('renderable', false);
    }
    Backgrid.Column.prototype.initialize.apply(this, arguments);
  },
});
