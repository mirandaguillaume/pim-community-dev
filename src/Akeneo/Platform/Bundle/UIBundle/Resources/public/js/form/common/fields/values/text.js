import BaseField from 'pim/form/common/fields/text';
import ValuesBehavior from 'pim/form/common/fields/values/values-behavior';

export default BaseField.extend({
  /**
   * {@inheritdoc}
   */
  updateModel(value) {
    ValuesBehavior.writeValue.call(this, BaseField, value);
  },

  /**
   * {@inheritdoc}
   */
  getModelValue() {
    return ValuesBehavior.readValue.call(this, BaseField);
  },
});
