function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
('use strict');

module.exports = StringCell.extend({
  className: 'AknGrid-bodyCell AknGrid-bodyCell--noWrap AknGrid-bodyCell--highlight',
  render: function () {
    StringCell.prototype.render.apply(this, arguments);
    this.$el.prop('title', this.$el.text());

    return this;
  },
});
