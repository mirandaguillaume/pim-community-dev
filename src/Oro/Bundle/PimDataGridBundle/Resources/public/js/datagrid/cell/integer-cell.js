import 'underscore';
import Backgrid from 'backgrid';

export default Backgrid.NumberCell.extend({
  /** @property {String} */
  style: 'decimal',

  /**
   * {@inheritdoc}
   */
  initialize: function () {
    this.decimals = 0;

    Backgrid.NumberCell.prototype.initialize.apply(this, arguments);
  },

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
