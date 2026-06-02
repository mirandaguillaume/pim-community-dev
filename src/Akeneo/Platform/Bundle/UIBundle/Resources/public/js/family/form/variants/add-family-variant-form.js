'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Routing = __pimInterop(require('routing'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/family-variant/add-variant-form'));

module.exports = BaseForm.extend({
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
