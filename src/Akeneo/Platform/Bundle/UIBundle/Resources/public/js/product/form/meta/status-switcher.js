import _ from 'underscore';
import __ from 'oro/translator';
import 'oro/mediator';
import BaseForm from 'pim/form';
import template from 'pim/template/product/meta/status-switcher';

export default BaseForm.extend({
  className: 'AknColumn-block AknDropdown',
  template: _.template(template),
  events: {
    'click .AknDropdown-menuLink': 'updateStatus',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    var status = this.getRoot().getFormData().enabled;

    this.$el.html(
      this.template({
        status: status,
        label: __('pim_common.status'),
        enabledLabel: __('pim_enrich.entity.product.module.status.enabled'),
        disabledLabel: __('pim_enrich.entity.product.module.status.disabled'),
      })
    );

    this.delegateEvents();

    return this;
  },

  /**
   * Update the current status of the product
   *
   * @param {Event} event
   */
  updateStatus: function (event) {
    var newStatus = event.currentTarget.dataset.status === 'enable';
    this.getFormModel().set('enabled', newStatus);
    this.getRoot().trigger('pim_enrich:form:entity:update_state');
    this.render();
  },
});
