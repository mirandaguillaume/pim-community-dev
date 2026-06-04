import $ from 'jquery';
import 'underscore';
import __ from 'oro/translator';
import UserContext from 'pim/user-context';
import router from 'pim/router';
import BaseForm from 'pim/form';

export default BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  addFieldExtension: function (event) {
    const entity = this.getFormData();
    if (undefined === entity.meta || null === entity.meta.family_variant) {
      return;
    }

    const levelAttributeCodes = entity.meta.attributes_for_this_level;
    const field = event.field;

    if (!levelAttributeCodes.includes(field.attribute.code)) {
      field.setEditable(false);
      this.updateFieldElements(field);
    }

    return this;
  },

  /**
   * Update the given field by adding element to it
   *
   * @param {Object} field
   */
  updateFieldElements: function (field) {
    const entity = this.getFormData();
    const isProduct = 'product' === entity.meta.model_type;

    let message = __('pim_enrich.entity.product_model.module.attribute.read_only_parent_attribute_from_common');
    let modelId = entity.meta.variant_navigation[0].selected.id;

    if (isProduct) {
      const uiLocale = UserContext.get('uiLocale');
      const comesFromParent = entity.meta.parent_attributes.includes(field.attribute.code);
      const hasTwoLevelsOfVariation = 3 === entity.meta.variant_navigation.length;

      if (comesFromParent && hasTwoLevelsOfVariation) {
        const parentAxesLabels = entity.meta.variant_navigation[1].axes[uiLocale];

        message = __('pim_enrich.entity.product_model.module.attribute.read_only_parent_attribute_from_model', {
          axes: parentAxesLabels,
        });
        modelId = entity.meta.variant_navigation[1].selected.id;
      }
    }

    const $element = $('<span class="AknFieldContainer-clickable">' + message + '</span>');

    $element.on('click', () => {
      this.redirectToModel(modelId);
    });

    field.addElement('footer', 'read_only_parent_attribute', $element);
  },

  redirectToModel: function (modelId) {
    const params = {id: modelId};
    const route = 'pim_enrich_product_model_edit';

    router.redirectToRoute(route, params);
  },
});
