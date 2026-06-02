'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/start-copy'));
var analytics = __pimInterop(require('pim/analytics'));
var FeatureFlags = __pimInterop(require('pim/feature-flags'));

module.exports = BaseForm.extend({
  template: _.template(template),
  className: 'AknDropdown-menuLink start-copying',
  events: {
    click: 'startCopy',
  },
  isCopying: false,

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:stop_copy', this.stopCopy.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render() {
    if (!FeatureFlags.isEnabled('free_trial')) {
      this.$el.html('');
      if (!this.isCopying) {
        this.$el.html(
          this.template({
            label: __('pim_enrich.entity.product.module.copy.label'),
          })
        );
      }
    }
  },

  /**
   * Triggers a new event to start copy
   */
  startCopy() {
    this.isCopying = true;
    this.getRoot().trigger('pim_enrich:form:start_copy');

    analytics.appcuesTrack('product:form:compare-clicked');
    this.render();
  },

  /**
   * Stops the copy and re-display the button
   */
  stopCopy() {
    this.isCopying = false;
    this.render();
  },
});
