import _ from 'underscore';
import 'jquery';
import BaseForm from 'pim/form';
import UserContext from 'pim/user-context';

export default BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.triggerModelUpdate);
    this.listenTo(this.getRoot(), 'pim_enrich:form:remove-attribute:after', this.triggerModelUpdate);
    this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.triggerModelUpdate);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   *
   * We need to set values to null if they don't match the current selected locale or scope.
   * We can't directly delete them as the structure (scope/channel) is used for validation.
   * These unused values will be removed later in the back office.
   */
  triggerModelUpdate: function () {
    var values = _.mapObject(this.getFormData().values, function (attributeValues) {
      return _.map(attributeValues, function (value) {
        if (null !== value.locale && UserContext.get('catalogLocale') !== value.locale) {
          value.data = null;
        }
        if (null !== value.scope && UserContext.get('catalogScope') !== value.scope) {
          value.data = null;
        }

        return value;
      });
    });
    this.setData({values: values}, {silent: true});

    this.getRoot().trigger('pim_enrich:mass_edit:model_updated', {values: values});

    return this;
  },
});
