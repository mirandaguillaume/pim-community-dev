'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseOperation = __pimInterop(require('pim/mass-edit-form/product/operation'));
var Select2Configurator = __pimInterop(require('pim/common/select2/family'));
var template = __pimInterop(require('pim/template/mass-edit/product/change-family'));
var initSelect2 = __pimInterop(require('pim/initselect2'));

module.exports = BaseOperation.extend({
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
