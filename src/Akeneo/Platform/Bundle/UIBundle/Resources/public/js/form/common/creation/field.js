function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/creation/field'));

module.exports = BaseForm.extend({
  template: _.template(template),
  dialog: null,
  events: {
    'keyup input': 'updateModel',
    'change input': 'updateModel',
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;
    this.identifier = this.config.identifier || 'code';

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * Model update callback
   */
  updateModel: function (event) {
    this.getFormModel().set(this.identifier, event.target.value || '');
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) this;

    const errors = this.getRoot().validationErrors || [];

    this.$el.html(
      this.template({
        identifier: this.identifier,
        label: __(this.config.label),
        requiredLabel: __('pim_common.required_label'),
        errors: errors.filter(error => {
          const id = this.identifier;
          const {path, attribute} = error;

          return id === path || id === attribute;
        }),
        value: this.getFormData()[this.identifier],
      })
    );

    this.delegateEvents();

    return this;
  },
});
