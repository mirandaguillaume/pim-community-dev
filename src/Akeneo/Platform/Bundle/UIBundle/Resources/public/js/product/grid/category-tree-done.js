import _ from 'underscore';
import __ from 'oro/translator';
import 'jquery';
import BaseForm from 'pim/form';
import template from 'pim/template/product/grid/category-tree-done';

export default BaseForm.extend({
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
