import BaseTranslation from 'pim/common/properties/translation';
import SecurityContext from 'pim/security-context';

export default BaseTranslation.extend({
  /**
   * {@inheritdoc}
   */
  isReadOnly: function () {
    return !SecurityContext.isGranted('pim_enrich_family_edit_properties');
  },
});
