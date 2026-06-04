import $ from 'jquery';
import __ from 'oro/translator';
import BaseForm from 'pim/form';

export default BaseForm.extend({
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
