function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Backgrid = __pimInterop(require('backgrid'));
var template = __pimInterop(require('pim/template/datagrid/cell/expand-history-cell'));
('use strict');

module.exports = Backgrid.StringCell.extend({
  /** @property */
  className: 'AknGrid-bodyCell AknGrid-expandable',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().html(
      this.template({
        value: this.model.get(this.column.get('name')),
      })
    );

    return this;
  },
});
