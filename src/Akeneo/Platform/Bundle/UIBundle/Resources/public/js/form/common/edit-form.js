import _ from 'underscore';
import 'oro/translator';
import Backbone from 'backbone';
import template from 'pim/template/common/default-template';
import BaseForm from 'pim/form';
import mediator from 'oro/mediator';
import FetcherRegistry from 'pim/fetcher-registry';
import FieldManager from 'pim/field-manager';
import 'pim/form-builder';
import RequireContext from 'require-context';
import messenger from 'oro/messenger';
import analytics from 'pim/analytics';

export default BaseForm.extend({
  template: _.template(template),
  scrollPosition: null,

  /**
   * {@inheritdoc}
   */
  initialize: function (options) {
    options = options || {};

    if (options.config && options.config.template) {
      this.template = _.template(RequireContext(options.config.template));
    }
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    mediator.clear('pim_enrich:form');
    Backbone.Router.prototype.once('route', this.unbindEvents);

    if (_.has(__moduleConfig, 'forwarded-events')) {
      this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
    }

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.displayError.bind(this));

    this.listenTo(this.getRoot(), 'pim_enrich:form:extension:render:before', () => {
      this.saveScroll();
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:extension:render:after', () => {
      this.setScroll();
    });

    this.listenTo(this.getRoot(), 'group:change', () => {
      this.resetScroll();
    });

    this.onExtensions(
      'save-buttons:register-button',
      function (button) {
        const saveButtonsExtension = this.getExtension('save-buttons');
        if (undefined === saveButtonsExtension) {
          throw Error(
            'edit-form extension should declare save-buttons extension to be able to use ' + 'save extension'
          );
        }
        saveButtonsExtension.trigger('save-buttons:add-button', button);
      }.bind(this)
    );

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }
    this.getRoot().trigger('pim_enrich:form:render:before');
    this.getRoot().trigger('pim_enrich:form:extension:render:before');

    this.$el.html(this.template());

    this.renderExtensions();

    this.getRoot().trigger('pim_enrich:form:render:after');
    this.getRoot().trigger('pim_enrich:form:extension:render:after');

    analytics.appcuesTrack('form:edit:opened', {
      code: this.code,
      model: this.model,
    });
  },

  /**
   * Save the current scroll position
   */
  saveScroll: function () {
    const containerElement = this.el.querySelector('.edit-form');
    if (containerElement) {
      this.scrollPosition = containerElement.scrollTop;
    }
  },

  /**
   * Set the scroll position to its former value
   */
  setScroll: function () {
    const containerElement = this.el.querySelector('.edit-form');
    if (containerElement && null !== this.scrollPosition) {
      containerElement.scrollTop = this.scrollPosition;
    }
  },

  resetScroll: function () {
    this.scrollPosition = 0;
  },

  /**
   * Clear the mediator
   */
  unbindEvents: function () {
    mediator.clear('pim_enrich:form');
  },

  /**
   * Clear the cached information
   */
  clearCache: function () {
    FetcherRegistry.clearAll();
    FieldManager.clearFields();
    this.render();
  },

  /**
   * Display validation error as flash message
   *
   * @param {Event} event
   */
  displayError: function (event) {
    if (!Array.isArray(event.response) && event.response.global) {
      messenger.notify('error', event.response.message);
    } else {
      _.each(event.response, function (error) {
        if (error.global) {
          messenger.notify('error', error.message);
        }
      });
    }
  },

  /**
   * Get header size of the form
   *
   * @return {number}
   */
  headerSize: function () {
    const header = this.el.querySelector('header.navigation');

    return null !== header ? header.offsetHeight : 0;
  },
});
