import $ from 'jquery';
import _ from 'underscore';
import BaseField from 'pim/form/common/fields/field';
import template from 'pim/template/form/common/fields/textarea';

export default BaseField.extend({
  template: _.template(template),
  events: {
    'keyup textarea': function (event) {
      this.errors = [];
      this.updateModel(this.getFieldValue(event.target));
    },
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(
      _.extend(templateContext, {
        value: this.getModelValue(),
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val();
  },
});
