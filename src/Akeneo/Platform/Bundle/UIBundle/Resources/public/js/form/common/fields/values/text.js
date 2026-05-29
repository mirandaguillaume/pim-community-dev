/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseField = __pimInterop(require('pim/form/common/fields/text'));
var ValuesBehavior = __pimInterop(require('pim/form/common/fields/values/values-behavior'));

module.exports = BaseField.extend({
  /**
   * {@inheritdoc}
   */
  updateModel(value) {
    ValuesBehavior.writeValue.call(this, BaseField, value);
  },

  /**
   * {@inheritdoc}
   */
  getModelValue() {
    return ValuesBehavior.readValue.call(this, BaseField);
  },
});
