'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Add this field extension to the given field event
   *
   * @param {Object} event
   *
   * @returns {Promise}
   */
  addFieldExtension: function (event) {
    var field = event.field;

    if (!field.attribute.is_locale_specific) {
      return;
    }

    if (!_.contains(field.attribute.available_locales, field.context.locale)) {
      this.updateFieldElements(field);
    } else {
      field.removeElement('field-input', 'input_placeholder');
    }

    return this;
  },

  /**
   * Update the given field by adding element to it
   *
   * @param {Object} field
   */
  updateFieldElements: function (field) {
    var message = __('pim_enrich.entity.product.module.attribute.locale_specific_unavailable');
    var element = '<span class="AknFieldContainer-unavailable">' + message + '</span>';

    field.addElement('field-input', 'input_placeholder', element);
  },
});
