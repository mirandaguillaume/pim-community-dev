'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var propertyAccessor = __pimInterop(require('pim/common/property'));
var template = __pimInterop(require('pim/template/form/tab/section'));
var LoadingMask = __pimInterop(require('oro/loading-mask'));

module.exports = BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),
  errors: [],

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
    this.$el.html(
      this.template({
        __: __,
        sectionTitle: this.config.label,
        dropZone: this.config.dropZone,
      })
    );

    this.renderExtensions();
  },

  /**
   * Get the validation errors for the given field
   *
   * @param {string} field
   *
   * @return {mixed}
   */
  getValidationErrorsForField: function (field) {
    return propertyAccessor.accessProperty(this.errors, field, []);
  },

  /**
   * Sets errors
   *
   * @param {Object} errors
   */
  setValidationErrors: function (errors) {
    this.errors = errors.response;
  },

  /**
   * Resets validation errors
   */
  resetValidationErrors: function () {
    this.errors = {};
    this.render();
  },

  /**
   * Shows the loading mask
   */
  showLoadingMask: function () {
    this.loadingMask = new LoadingMask();
    this.loadingMask.render().$el.appendTo(this.getRoot().$el).show();
  },

  /**
   * Hides the loading mask
   */
  hideLoadingMask: function () {
    this.loadingMask.hide().$el.remove();
  },
});
