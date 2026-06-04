import Backgrid from 'backgrid';
import CellFormatter from 'oro/datagrid/cell-formatter';
('use strict');

export default Backgrid.StringCell.extend({
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
