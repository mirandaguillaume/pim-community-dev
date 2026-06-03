function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteAction = __pimInterop(require('oro/datagrid/delete-action'));
var Router = __pimInterop(require('pim/router'));
var SecurityContext = __pimInterop(require('pim/security-context'));

module.exports = DeleteAction.extend({
  /** @property {Boolean} */
  noHref: true,

  /**
   * {@inheritdoc}
   */
  initialize() {
    this.launcherOptions.enabled = this.isEnabled();

    return DeleteAction.prototype.initialize.apply(this, arguments);
  },

  getLink() {
    const productType = this.model.get('document_type');
    const id = this.model.get('technical_id');

    if (productType === 'product') {
      return Router.generate('pim_enrich_product_rest_remove', {uuid: id});
    }

    return Router.generate('pim_enrich_' + productType + '_rest_remove', {id});
  },

  getEntityHint() {
    return this.model.get('document_type').replace('_', ' ');
  },

  /**
   * {@inheritdoc}
   */
  isEnabled() {
    const productType = this.model.get('document_type');

    return (
      (SecurityContext.isGranted('pim_enrich_product_model_remove') && productType === 'product_model') ||
      (SecurityContext.isGranted('pim_enrich_product_remove') && productType === 'product')
    );
  },
});
