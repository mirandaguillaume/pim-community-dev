import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import * as toFillFieldProvider from 'pim/provider/to-fill-field-provider';
import UserContext from 'pim/user-context';

export default BaseForm.extend({
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
