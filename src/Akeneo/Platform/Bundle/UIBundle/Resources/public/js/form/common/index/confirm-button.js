'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var Routing = __pimInterop(require('routing'));
var template = __pimInterop(require('pim/template/form/index/confirm-button'));

module.exports = BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config || {};

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        buttonClass: this.config.buttonClass,
        buttonLabel: __(this.config.buttonLabel),
        title: __(this.config.title),
        message: __(this.config.message),
        url: Routing.generate(this.config.url),
        redirectUrl: Routing.generate(this.config.redirectUrl),
        errorMessage: __(this.config.errorMessage),
        successMessage: __(this.config.successMessage),
        subTitle: __(this.config.subTitle),
      })
    );

    this.renderExtensions();

    return this;
  },
});
