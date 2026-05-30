function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DatagridDateTimeCell = __pimInterop(require('oro/datagrid/datetime-cell'));
('use strict');

module.exports = DatagridDateTimeCell.extend({type: 'date'});
