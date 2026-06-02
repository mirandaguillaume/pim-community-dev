'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Field = __pimInterop(require('pim/field'));
var _ = __pimInterop(require('underscore'));
var fieldTemplate = __pimInterop(require('pim/template/product/field/number'));

module.exports = Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first input[type="text"]': 'updateModel',
  },
  renderInput: function (context) {
    return this.fieldTemplate(context);
  },
  updateModel: function () {
    var data = this.$('.field-input:first input[type="text"]').val();

    if ('' === data) {
      data = this.attribute.empty_value;
    }

    this.setCurrentValue(data);
  },
});
