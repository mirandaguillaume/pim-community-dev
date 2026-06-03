'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form/common/edit-form'));

module.exports = BaseForm.extend({
  readOnly: false,

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = _.extend({}, meta.config);

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * Called to reset the operation module
   */
  reset: function () {},

  /**
   * The label displayed in the operation list
   *
   * @return {String}
   */
  getLabel: function () {
    return __(this.config.label);
  },

  /**
   * Returns the title of the operation
   *
   * @returns {String}
   */
  getTitle() {
    return __(this.config.title);
  },

  /**
   * Returns the label with the count of impacted elements
   *
   * @returns {String}
   */
  getLabelCount: function () {
    const itemsCount = this.getFormData().itemsCount;

    return __(this.config.labelCount, {itemsCount}, itemsCount);
  },

  /**
   * Returns the main illustration of this operation
   *
   * @returns {String}
   */
  getIllustrationClass: function () {
    return this.config.illustration || 'products';
  },

  /**
   * Get the operation description
   *
   * @return {String}
   */
  getDescription: function () {
    return __(this.config.description);
  },

  /**
   * Get the operation code
   *
   * @return {String}
   */
  getCode: function () {
    return this.config.code;
  },

  /**
   * Get the operation icon
   *
   * @return {String}
   */
  getIcon: function () {
    return this.config.icon;
  },

  /**
   * Get job instance code to launch
   *
   * @return {String}
   */
  getJobInstanceCode: function () {
    return this.config.jobInstanceCode;
  },

  /**
   * Called when the operation should switch from read only or edit
   *
   * @param {boolean} readOnly
   */
  setReadOnly: function (readOnly) {
    this.readOnly = readOnly;

    this.triggerExtensions('mass-edit:update-read-only', this.readOnly);
  },

  /**
   * Called before the confirmation step to validate the model
   *
   * @return {promise}
   */
  validate: function () {
    return $.Deferred().resolve(true);
  },
});
