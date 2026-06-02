/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseField = __pimInterop(require('pim/form/common/fields/boolean'));

module.exports = BaseField.extend({
  /**
   * {@inheritdoc}
   */
  updateModel: function () {
    BaseField.prototype.updateModel.apply(this, arguments);

    if (false === this.getFormData().is_locale_specific) {
      this.setData({available_locales: []}, {silent: true});
    }
  },
});
