function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Backgrid = __pimInterop(require('backgrid'));
var CellFormatter = __pimInterop(require('oro/datagrid/cell-formatter'));
var __ = __pimInterop(require('oro/translator'));
('use strict');

module.exports = Backgrid.StringCell.extend({
  formatter: new CellFormatter(),
  render: function render() {
    const attributeTypeLabel = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    this.$el.empty();
    this.el.dataset.column = this.column.get('name');
    const isMain = this.model.attributes.main_identifier === '1';
    if (isMain) {
      this.$el.html(
        `${attributeTypeLabel} <div class="AknBadge AknBadge--info">${__(
          'pim_datagrid.cells.attribute-type.main'
        )}</div>`
      );
    } else {
      this.$el.text(attributeTypeLabel);
    }
    this.delegateEvents();

    return this;
  },
});
