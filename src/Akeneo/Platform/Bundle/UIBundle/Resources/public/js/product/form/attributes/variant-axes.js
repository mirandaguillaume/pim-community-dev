import 'underscore';
import __ from 'oro/translator';
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

    const axesAttributeCodes = entity.meta.attributes_axes;
    const field = event.field;

    if (axesAttributeCodes.includes(field.attribute.code)) {
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
    const message = '(' + __('pim_enrich.entity.product_model.module.variant_axis.label') + ')';
    const element = '<span class="">' + message + '</span>';

    field.addElement('label', 'variant_axis', element);
  },
});
