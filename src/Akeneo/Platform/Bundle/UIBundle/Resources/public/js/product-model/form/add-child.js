import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import Routing from 'routing';
import template from 'pim/template/product-model-edit-form/add-child-form';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render() {
    const illustrationClass = this.getIllustrationClass();
    this.$el.html(
      this.template({
        illustrationClass,
        okText: __('pim_common.save'),
      })
    );
    this.renderExtensions();
  },

  /**
   * Get the correct illustration class for products or product models
   *
   * @return {String}
   */
  getIllustrationClass() {
    const formData = this.getFormData();
    const hasFamilyVariant = formData.hasOwnProperty('family_variant');

    return hasFamilyVariant ? 'product-model' : 'products';
  },

  /**
   * Save the product model child in the backend.
   *
   * @param {String} route
   *
   * @return {Promise}
   */
  saveProductModelChild(route) {
    this.trigger('pim_enrich:form:entity:pre_save');

    return $.post(Routing.generate(route), JSON.stringify(this.getFormData())).fail(xhr => {
      this.trigger('pim_enrich:form:entity:validation_error', xhr.responseJSON.values);
    });
  },
});
