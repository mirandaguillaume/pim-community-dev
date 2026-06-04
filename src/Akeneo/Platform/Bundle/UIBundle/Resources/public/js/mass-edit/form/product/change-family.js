import _ from 'underscore';
import __ from 'oro/translator';
import BaseOperation from 'pim/mass-edit-form/product/operation';
import Select2Configurator from 'pim/common/select2/family';
import template from 'pim/template/mass-edit/product/change-family';
import initSelect2 from 'pim/initselect2';

export default BaseOperation.extend({
  template: _.template(template),
  events: {
    'change .family': 'updateModel',
  },

  /**
   * {@inheritdoc}
   */
  reset: function () {
    this.setValue(null);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        readOnly: this.readOnly,
        value: this.getValue(),
        label: __('pim_enrich.entity.family.uppercase_label'),
      })
    );

    var options = Select2Configurator.getConfig(this.getValue());

    initSelect2.init(this.$('.family'), options);

    return this;
  },

  /**
   * Update the form model from a dom event
   *
   * @param {event} event
   */
  updateModel: function (event) {
    this.setValue(event.target.value);
  },

  /**
   * update the form model
   *
   * @param {string} family
   */
  setValue: function (family) {
    var data = this.getFormData();

    data.actions = [
      {
        field: 'family',
        value: family,
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
    var action = _.findWhere(this.getFormData().actions, {field: 'family'});

    return action ? action.value : null;
  },
});
