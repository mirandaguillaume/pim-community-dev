'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Backbone = __pimInterop(require('backbone'));
var Dialog = __pimInterop(require('pim/dialog'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/state'));

module.exports = BaseForm.extend({
  className: 'updated-status',
  template: _.template(template),
  state: null,
  linkSelector: 'a[href^="/"]:not(".no-hash")',

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = _.extend(
      {},
      {
        confirmationMessage: 'pim_enrich.entity.fallback.module.edit.discard_changes',
        confirmationTitle: 'pim_enrich.entity.fallback.module.edit.leave',
        message: 'pim_common.entity_updated',
      },
      meta.config
    );

    this.confirmationMessage = __(this.config.confirmationMessage, {entity: __(this.config.entity)});
    this.confirmationTitle = __(this.config.confirmationTitle);
    this.message = __(this.config.message);

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * @inheritdoc
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.collectAndRender);
    this.listenTo(this.getRoot(), 'pim_enrich:form:state:confirm', this.onConfirmation);
    this.listenTo(this.getRoot(), 'pim_enrich:form:can-leave', this.linkClicked);
    $(window).on('beforeunload', this.beforeUnload.bind(this));

    Backbone.Router.prototype.on('route', this.unbindEvents);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Detach event listeners
   */
  unbindEvents: function () {
    $(window).off('beforeunload', this.beforeUnload);
  },

  /**
   * @inheritdoc
   */
  render: function () {
    if (null === this.state || undefined === this.state) {
      this.collectState();
    }

    this.$el
      .html(
        this.template({
          message: this.message,
        })
      )
      .css('display', this.hasModelChanged() ? '' : 'none');

    return this;
  },

  /**
   * Store a stringified representation of the form model for further comparisons
   */
  collectState: function () {
    this.state = JSON.stringify(this.getFormData());
  },

  /**
   * Force collect state and re-render
   */
  collectAndRender: function () {
    this.collectState();
    this.render();
  },

  /**
   * Callback triggered on beforeunload event
   */
  beforeUnload: function () {
    if (this.hasModelChanged()) {
      return this.confirmationMessage;
    }
  },

  /**
   * Callback triggered on any link click event to ask confirmation if there are unsaved changes
   *
   * @param {Object} event
   *
   * @return {boolean}
   */
  linkClicked: function (event) {
    if (this.hasModelChanged()) {
      event.canLeave = confirm(this.confirmationMessage);
    }
  },

  /**
   * Check if current form model has changed compared to the stored model state
   *
   * @return {boolean}
   */
  hasModelChanged: function () {
    return this.state !== JSON.stringify(this.getFormData());
  },

  /**
   * Display a dialog modal to ask an action confirmation if model has changed
   *
   * @param {string} message
   * @param {string} title
   * @param {function} action
   */
  confirmAction: function (message, title, action) {
    if (this.hasModelChanged()) {
      Dialog.confirm(message, title, action);
    } else {
      action();
    }
  },

  /**
   * Callback that can be triggered from anywhere to ask an action confirmation
   *
   * @param {Object} event
   */
  onConfirmation: function (event) {
    this.confirmAction(event.message || this.confirmationMessage, event.title || this.confirmationTitle, event.action);
  },
});
