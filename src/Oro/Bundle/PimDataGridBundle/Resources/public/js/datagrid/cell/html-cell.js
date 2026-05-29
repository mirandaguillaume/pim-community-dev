function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
('use strict');

module.exports = StringCell.extend({
  /**
   * Render a text string in a table cell. The text is converted from the
   * model's raw value for this cell's column.
   */
  render: function () {
    this.$el.empty().html(this.formatter.fromRaw(this.model.get(this.column.get('name'))));
    return this;
  },
});
