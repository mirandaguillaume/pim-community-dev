import Field from 'pim/field';
import _ from 'underscore';
import fieldTemplate from 'pim/template/product/field/text';

export default Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first input[type="text"]': 'updateModel',
  },
  renderInput: function (context) {
    return this.fieldTemplate(context);
  },
  updateModel: function () {
    var data = this.$('.field-input:first input[type="text"]').val();
    data = '' === data ? this.attribute.empty_value : data;

    this.setCurrentValue(data);
  },
});
