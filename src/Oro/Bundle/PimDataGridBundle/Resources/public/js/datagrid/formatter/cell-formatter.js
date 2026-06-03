function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Backgrid = __pimInterop(require('backgrid'));
('use strict');

/**
 * Cell formatter with fixed fromRaw method
 *
 * @export  oro/datagrid/cell-formatter
 * @class   oro.datagrid.CellFormatter
 * @extends Backgrid.CellFormatter
 */
var CellFormatter = function () {};

CellFormatter.prototype = new Backgrid.CellFormatter();

_.extend(CellFormatter.prototype, {
  /**
   * @inheritDoc
   */
  fromRaw: function (rawData) {
    if (rawData == null) {
      return '';
    }
    return Backgrid.CellFormatter.prototype.fromRaw.apply(this, arguments);
  },
});

module.exports = CellFormatter;
