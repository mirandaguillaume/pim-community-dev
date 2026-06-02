'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
require('jquery');
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.triggerModelUpdate);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.triggerModelUpdate);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Update the root model after fake product save
   */
  triggerModelUpdate: function () {
    var data = this.getFormData();
    data.attributes = _.pluck(data.attributes, 'code');
    delete data.meta;

    this.getRoot().trigger('pim_enrich:mass_edit:model_updated', data);

    return this;
  },
});
