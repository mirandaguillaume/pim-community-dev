import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseField from 'pim/form/common/fields/field';
import template from 'pim/template/form/common/fields/boolean';
import 'bootstrap.bootstrapswitch';

export default BaseField.extend({
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
