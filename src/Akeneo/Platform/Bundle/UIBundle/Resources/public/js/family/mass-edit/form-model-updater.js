import _ from 'underscore';
import 'jquery';
import BaseForm from 'pim/form';

export default BaseForm.extend({
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
