'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var Routing = __pimInterop(require('routing'));
var router = __pimInterop(require('pim/router'));
var propertyAccessor = __pimInterop(require('pim/common/property'));
var template = __pimInterop(require('pim/template/form/redirect'));

module.exports = BaseForm.extend({
  template: _.template(template),
  events: {
    click: 'redirect',
  },

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
  render: function () {
    this.isVisible().then(
      function (isVisible) {
        if (!isVisible) {
          return this;
        }

        this.$el.html(
          this.template({
            label: __(this.config.label),
            buttonClass: this.config.buttonClass || 'AknButton--action',
            title: this.config.title ? __(this.config.title) : '',
          })
        );
      }.bind(this)
    );

    return this;
  },

  /**
   * Redirect to the route given in the config
   */
  redirect: function () {
    router.redirect(this.getUrl());
  },

  /**
   * Get the route to redirect to
   *
   * @return {string}
   */
  getUrl: function () {
    var params = {};
    if (this.config.identifier) {
      params[this.config.identifier.name] = propertyAccessor.accessProperty(
        this.getFormData(),
        this.config.identifier.path
      );
    }

    return Routing.generate(this.config.route, params);
  },

  /**
   * Should this extension render
   *
   * @return {Promise}
   */
  isVisible: function () {
    return $.Deferred().resolve(true).promise();
  },
});
