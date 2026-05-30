function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
var __ = __pimInterop(require('oro/translator'));
('use strict');

module.exports = StringCell.extend({
  render: function () {
    const columnValue = this.model.get(this.column.get('name'));
    const value = this.formatter.fromRaw(columnValue);
    const label = true === value || 'true' === value || '1' === value ? '<strong>' + __('Yes') + '</strong>' : __('No');

    this.$el.empty().html(label);

    return this;
  },
});
