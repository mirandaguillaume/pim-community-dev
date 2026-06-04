import _ from 'underscore';
import $ from 'jquery';
import BaseRow from 'oro/datagrid/product-row';
import 'pim/media-url-generator';
import thumbnailTemplate from 'pim/template/product/tab/associated-product-row';
import mediator from 'oro/mediator';
import SecurityContext from 'pim/security-context';
import router from 'pim/router';

export default BaseRow.extend({
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
