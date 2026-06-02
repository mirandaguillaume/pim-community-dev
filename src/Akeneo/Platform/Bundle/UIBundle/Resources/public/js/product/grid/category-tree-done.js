function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
require('jquery');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/grid/category-tree-done'));

module.exports = BaseForm.extend({
  className: 'AknDefault-thirdColumnButton',
  template: _.template(template),
  events: {
    click: 'toggleThirdColumn',
  },

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.append(
      this.template({
        label: __('pim_common.done'),
      })
    );
  },

  /**
   * Toggles the third column
   */
  toggleThirdColumn() {
    this.getRoot().trigger('grid:third_column:toggle');
  },
});
