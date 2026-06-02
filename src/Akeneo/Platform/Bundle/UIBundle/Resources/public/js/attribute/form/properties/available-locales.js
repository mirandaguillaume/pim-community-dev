/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseAvailableLocales = __pimInterop(require('pim/form/common/fields/available-locales'));

module.exports = BaseAvailableLocales.extend({
  /**
   * {@inheritdoc}
   *
   * This field shouldn't be displayed if the attribute is not locale specific.
   */
  isVisible: function () {
    return undefined !== this.getFormData().is_locale_specific && this.getFormData().is_locale_specific;
  },
});
