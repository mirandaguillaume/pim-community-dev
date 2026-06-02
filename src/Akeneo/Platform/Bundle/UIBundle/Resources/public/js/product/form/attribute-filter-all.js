'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
  /**
   * @returns {String}
   */
  getCode() {
    return 'all';
  },

  /**
   * @returns {String}
   */
  getLabel() {
    return __('pim_enrich.entity.product.module.attribute_filter.all');
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
    return $.Deferred().resolve(values).promise();
  },
});
