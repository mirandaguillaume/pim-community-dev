function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var Backgrid = __pimInterop(require('backgrid'));
('use strict');

module.exports = Backgrid.NumberCell.extend({
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
