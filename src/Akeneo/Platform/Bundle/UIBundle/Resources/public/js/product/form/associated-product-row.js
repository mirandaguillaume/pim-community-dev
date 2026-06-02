function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var $ = __pimInterop(require('jquery'));
var BaseRow = __pimInterop(require('oro/datagrid/product-row'));
require('pim/media-url-generator');
var thumbnailTemplate = __pimInterop(require('pim/template/product/tab/associated-product-row'));
var mediator = __pimInterop(require('oro/mediator'));
var SecurityContext = __pimInterop(require('pim/security-context'));
var router = __pimInterop(require('pim/router'));

module.exports = BaseRow.extend({
  thumbnailTemplate: _.template(thumbnailTemplate),

  /**
   * Return true if the user can remove the association, false otherwise.
   *
   * The use can remove an association if he has the permission and if the association
   * does not come from inheritance.
   *
   * @return {Boolean}
   */
  canRemoveAssociation() {
    const permissionGranted = SecurityContext.isGranted('pim_enrich_associations_remove');
    const fromInheritance = this.model.get('from_inheritance');

    return permissionGranted && !fromInheritance;
  },

  /**
   * {@inheritdoc}
   */
  getTemplateOptions() {
    const isProductModel = this.isProductModel();
    const id = this.model.id.replace(/product-model-|product-/g, '');
    const label = this.model.get('label') || `[${id}]`;
    const canRemoveAssociation = this.canRemoveAssociation();

    return {
      useLayerStyle: isProductModel,
      label,
      identifier: this.model.get('identifier') ?? `[${id}]`,
      imagePath: this.getThumbnailImagePath(),
      canRemoveAssociation,
      redirectUrl: router.generate(
        this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
        this.isProductModel() ? {id} : {uuid: id}
      ),
    };
  },

  /**
   * {@inheritdoc}
   */
  render() {
    BaseRow.prototype.render.call(this, arguments);

    const row = this.renderedRow;

    row.off('click');

    $('.AknIconButton--remove', row).on('click', () => {
      mediator.trigger('datagrid:unselectModel:association-product-grid', this.model);
      mediator.trigger('datagrid:unselectModel:association-product-model-grid', this.model);
      row.remove();
    });
  },

  /**
   * {@inheritdoc}
   */
  getRenderableColumns() {
    return [this.getCompletenessCellType()];
  },
});
