import 'underscore';
import Backgrid from 'backgrid';

export default Backgrid.NumberCell.extend({
  /** @property {String} */
  style: 'decimal',

  /**
   * @inheritDoc
   */
  enterEditMode: function (e) {
    if (this.column.get('editable')) {
      e.stopPropagation();
    }
    return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
  },
});
