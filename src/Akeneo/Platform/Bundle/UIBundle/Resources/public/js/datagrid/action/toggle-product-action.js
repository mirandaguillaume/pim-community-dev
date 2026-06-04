import AjaxAction from 'oro/datagrid/ajax-action';
import Router from 'pim/router';

export default AjaxAction.extend({
  /** @property {Boolean} */
  noHref: true,

  /**
   * {@inheritdoc}
   */
  initialize() {
    this.launcherOptions.enabled = this.isEnabled();

    AjaxAction.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  isEnabled() {
    return this.model.get('document_type') !== 'product_model';
  },

  /**
   * {@inheritdoc}
   */
  getMethod: function () {
    return 'POST';
  },

  getLink() {
    const productType = this.model.get('document_type');
    const id = this.model.get('technical_id');

    if (productType === 'product') {
      return Router.generate('pim_enrich_product_toggle_status', {uuid: id});
    }
  },
});
