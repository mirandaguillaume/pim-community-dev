function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Backgrid = __pimInterop(require('backgrid'));
var CellFormatter = __pimInterop(require('oro/datagrid/cell-formatter'));
('use strict');

module.exports = Backgrid.StringCell.extend({
  /**
       @property {(Backgrid.CellFormatter|Object|string)}
       */
  formatter: new CellFormatter(),

  /**
   * @inheritDoc
   */
  enterEditMode: function (e) {
    if (this.column.get('editable')) {
      e.preventDefault();
      e.stopPropagation();
    }
    return Backgrid.StringCell.prototype.enterEditMode.apply(this, arguments);
  },
});
