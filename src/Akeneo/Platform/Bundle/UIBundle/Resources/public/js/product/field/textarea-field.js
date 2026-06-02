'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Field = __pimInterop(require('pim/field'));
var _ = __pimInterop(require('underscore'));
var fieldTemplate = __pimInterop(require('pim/template/product/field/textarea'));

module.exports = Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first textarea': 'updateModel',
  },

  /**
   * @inheritDoc
   */
  renderInput: function (context) {
    return this.fieldTemplate(context);
  },

  /**
   * @inheritDoc
   */
  updateModel: function () {
    var data = this.$('.field-input:first textarea:first').val();
    data = '' === data ? this.attribute.empty_value : data;

    this.setCurrentValue(data);
  },

  /**
   * @inheritDoc
   */
  setFocus: function () {
    this.$('.field-input:first textarea').focus();
  },
});
