import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import Routing from 'routing';
import BaseForm from 'pim/form';
import template from 'pim/template/family-variant/add-variant-form';

export default BaseForm.extend({
  template: _.template(template),

  render() {
    // This can probably be refactored using 'pim/template/common/modal-with-illustration'
    this.$el.html(
      this.template({
        okLabel: __('pim_common.create'),
      })
    );
    this.renderExtensions();
  },

  /**
   * Save the family variant in the backend.
   */
  saveFamilyVariant() {
    this.trigger('pim_enrich:form:entity:pre_save');

    return $.post(Routing.generate('pim_enrich_family_variant_rest_create'), JSON.stringify(this.getFormData())).fail(
      xhr => {
        this.trigger('pim_enrich:form:entity:validation_error', xhr.responseJSON);
      }
    );
  },
});
