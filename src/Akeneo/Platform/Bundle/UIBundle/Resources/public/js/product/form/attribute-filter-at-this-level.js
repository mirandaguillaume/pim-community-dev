'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
  /**
   * @returns {String}
   */
  getCode() {
    return 'at-this-level';
  },

  /**
   * @returns {String}
   */
  getLabel() {
    return __('pim_enrich.entity.product.module.attribute_filter.at_this_level');
  },

  /**
   * @returns {Boolean}
   */
  isVisible() {
    const meta = this.getFormData().meta;

    return null !== meta.level && meta.level > 0;
  },

  /**
   * @param {Object} values
   *
   * @returns {Promise}
   */
  filterValues(values) {
    const valuesToFill = _.pick(values, this.getFormData().meta.attributes_for_this_level);

    return $.Deferred().resolve(valuesToFill).promise();
  },
});
