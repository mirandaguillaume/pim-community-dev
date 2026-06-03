'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseField = __pimInterop(require('pim/job/common/edit/field/field'));
var fieldTemplate = __pimInterop(require('pim/template/export/common/edit/field/text'));

module.exports = BaseField.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change input': 'updateState',
  },

  /**
   * Get the field dom value
   *
   * @return {string}
   */
  getFieldValue: function () {
    return this.$('input').val();
  },
});
