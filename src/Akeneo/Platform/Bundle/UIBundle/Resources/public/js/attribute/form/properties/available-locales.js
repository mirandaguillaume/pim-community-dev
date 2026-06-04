import BaseAvailableLocales from 'pim/form/common/fields/available-locales';

export default BaseAvailableLocales.extend({
  /**
   * {@inheritdoc}
   *
   * This field shouldn't be displayed if the attribute is not locale specific.
   */
  isVisible: function () {
    return undefined !== this.getFormData().is_locale_specific && this.getFormData().is_locale_specific;
  },
});
