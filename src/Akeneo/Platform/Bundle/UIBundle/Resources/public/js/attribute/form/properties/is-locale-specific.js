import BaseField from 'pim/form/common/fields/boolean';

export default BaseField.extend({
  /**
   * {@inheritdoc}
   */
  updateModel: function () {
    BaseField.prototype.updateModel.apply(this, arguments);

    if (false === this.getFormData().is_locale_specific) {
      this.setData({available_locales: []}, {silent: true});
    }
  },
});
