'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/properties/input'));

module.exports = BaseForm.extend({
  className: 'input',
  template: _.template(template),
  errors: [],

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(
      this.template({
        fieldName: this.config.fieldBaseId + 'code',
        className: 'family-code',
        value: this.getFormData().code,
        errors: [],
        label: __(this.config.label),
        requiredLabel: __('pim_common.required_label'),
        isRequired: true,
        isReadOnly: true,
      })
    );

    this.renderExtensions();
  },
});
