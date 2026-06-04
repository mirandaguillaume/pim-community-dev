import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/creation/field';

export default BaseForm.extend({
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
