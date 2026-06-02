'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
require('backbone');
var template = __pimInterop(require('pim/template/export/product/edit/content'));
var BaseForm = __pimInterop(require('pim/form'));
var analytics = __pimInterop(require('pim/analytics'));
var {filterErrors} = __pimInterop(require('@akeneo-pim-community/shared'));

module.exports = BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.getTabCode(),
      label: __(this.config.tabTitle),
    });
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render.bind(this));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const validationErrors = event.response.normalized_errors;
      const errors = filterErrors(validationErrors, '[filters]');
      if (errors.length > 0) {
        this.getRoot().trigger('pim_enrich:form:form-tabs:add-errors', {
          tabCode: this.getTabCode(),
          errors,
        });
      }
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  getTabCode: function () {
    return this.config.tabCode ? this.config.tabCode : this.code;
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(this.template({}));

    analytics.appcuesTrack('export-profile:product:content-tab-opened', {
      code: this.code,
    });

    this.renderExtensions();
  },
});
