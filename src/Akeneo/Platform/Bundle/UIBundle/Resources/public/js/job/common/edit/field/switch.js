'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseField = __pimInterop(require('pim/job/common/edit/field/field'));
var fieldTemplate = __pimInterop(require('pim/template/export/common/edit/field/switch'));
require('bootstrap.bootstrapswitch');

module.exports = BaseField.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change input': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    BaseField.prototype.render.apply(this, arguments);

    this.$('.switch').bootstrapSwitch();
  },

  /**
   * Get the field dom value
   *
   * @return {string}
   */
  getFieldValue: function () {
    return this.$('input[type="checkbox"]').prop('checked');
  },
});
