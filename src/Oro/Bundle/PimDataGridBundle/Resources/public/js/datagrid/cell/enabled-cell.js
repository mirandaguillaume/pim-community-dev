import StringCell from 'oro/datagrid/string-cell';
import __ from 'oro/translator';
import template from 'pim/template/datagrid/cell/enabled-cell';

export default StringCell.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    if ('product_model' === this.model.get('document_type')) {
      // PIM-6493: the value should be calculated depending on the the model subtree.
      this.$el.empty().html('');

      return this;
    }

    const value = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    const enabled = true === value ? 'enabled' : 'disabled';
    const label =
      true === value
        ? __('pim_enrich.entity.product.module.status.enabled')
        : __('pim_enrich.entity.product.module.status.disabled');

    this.$el.empty().html(this.template({enabled, label}));

    return this;
  },
});
