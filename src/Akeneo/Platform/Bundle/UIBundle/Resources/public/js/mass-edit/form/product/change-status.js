import _ from 'underscore';
import __ from 'oro/translator';
import BaseOperation from 'pim/mass-edit-form/product/operation';
import template from 'pim/template/mass-edit/product/change-status';
import 'bootstrap.bootstrapswitch';

export default BaseOperation.extend({
  template: _.template(template),
  events: {
    'change .switch': 'updateModel',
  },

  /**
   * {@inheritdoc}
   */
  reset: function () {
    this.setValue(false);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        value: this.getValue(),
        readOnly: this.readOnly,
        labels: {
          on: __('pim_common.yes'),
          off: __('pim_common.no'),
          field: __('pim_enrich.mass_edit.product.operation.change_status.field'),
        },
      })
    );

    this.$('.switch').bootstrapSwitch();

    return this;
  },

  /**
   * Update the form model from a dom event
   *
   * @param {event} event
   */
  updateModel: function (event) {
    this.setValue(event.target.checked);
  },

  /**
   * update the form model
   *
   * @param {string} family
   */
  setValue: function (enabled) {
    var data = this.getFormData();

    data.actions = [
      {
        field: 'enabled',
        value: enabled,
      },
    ];

    this.setData(data);
  },

  /**
   * Get the current model value
   *
   * @return {string}
   */
  getValue: function () {
    var action = _.findWhere(this.getFormData().actions, {field: 'enabled'});

    return action ? action.value : null;
  },
});
