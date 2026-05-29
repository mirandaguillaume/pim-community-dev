function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var __ = __pimInterop(require('oro/translator'));
var Backgrid = __pimInterop(require('backgrid'));
var template = __pimInterop(require('pim/template/datagrid/cell/history-diff-cell'));
('use strict');

module.exports = Backgrid.StringCell.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.el.setAttribute('colspan', 4);
    this.$el.empty();
    this.$el.html(
      this.template({
        changes: this.model.get(this.column.get('name')),
        __,
      })
    );
    this.delegateEvents();

    return this;
  },
});
