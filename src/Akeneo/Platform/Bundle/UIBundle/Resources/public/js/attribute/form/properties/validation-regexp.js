import BaseField from 'pim/form/common/fields/text';

export default BaseField.extend({
  /**
   * {@inheritdoc}
   *
   * This field should be displayed only when the validation rule is set to "regular expression".
   */
  isVisible: function () {
    return 'regexp' === this.getFormData().validation_rule;
  },
});
