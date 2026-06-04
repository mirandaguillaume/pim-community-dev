import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/product/start-copy';
import analytics from 'pim/analytics';
import FeatureFlags from 'pim/feature-flags';

export default BaseForm.extend({
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
