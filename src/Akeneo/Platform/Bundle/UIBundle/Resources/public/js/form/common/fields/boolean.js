/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseField = __pimInterop(require('pim/form/common/fields/field'));
var template = __pimInterop(require('pim/template/form/common/fields/boolean'));
require('bootstrap.bootstrapswitch');

module.exports = BaseField.extend({
  events: {
    'change input': function (event) {
      this.errors = [];
      this.updateModel(this.getFieldValue(event.target));
      this.getRoot().render();
    },
  },
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    if (undefined === this.getModelValue() && _.has(this.config, 'defaultValue')) {
      this.updateModel(this.config.defaultValue);
    }

    return this.template(
      _.extend(templateContext, {
        value: this.getModelValue(),
        labels: {
          on: __('pim_common.yes'),
          off: __('pim_common.no'),
        },
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.switch').bootstrapSwitch();
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).is(':checked');
  },
});
