import _ from 'underscore';
import __ from 'oro/translator';
import template from 'pim/template/export/product/edit/content/structure';
import BaseForm from 'pim/form';
import propertyAccessor from 'pim/common/property';

export default BaseForm.extend({
  className: 'structure-filters',
  errors: {},
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.setValidationErrors.bind(this));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.resetValidationErrors.bind(this));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Set the validation errors after validation fail
   *
   * @param {event} event
   */
  setValidationErrors: function (event) {
    this.errors = event.response;
  },

  /**
   * Rest validation error after fetch
   */
  resetValidationErrors: function () {
    this.errors = {};
  },

  /**
   * Get the validtion errors for the given field
   *
   * @param {string} field
   *
   * @return {mixed}
   */
  getValidationErrorsForField: function (field) {
    return propertyAccessor.accessProperty(this.errors, 'configuration.filters.structure.' + field, []);
  },

  /**
   * Renders this view.
   *
   * @return {Object}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }
    this.$el.html(this.template({__: __}));

    this.renderExtensions();

    return this;
  },
});
