'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var toFillFieldProvider = __pimInterop(require('pim/provider/to-fill-field-provider'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseForm.extend({
  /**
   * @returns {String}
   */
  getCode() {
    return 'missing_required';
  },

  /**
   * @returns {String}
   */
  getLabel() {
    return __('pim_enrich.entity.product.module.attribute_filter.missing_required');
  },

  /**
   * @returns {Boolean}
   */
  isVisible() {
    return true;
  },

  /**
   * @param {Object} values
   *
   * @returns {Promise}
   */
  filterValues(values) {
    const scope = UserContext.get('catalogScope');
    const locale = UserContext.get('catalogLocale');

    const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(this.getFormData(), scope, locale);
    const valuesToFill = _.pick(values, fieldsToFill);

    return $.Deferred().resolve(valuesToFill).promise();
  },
});
