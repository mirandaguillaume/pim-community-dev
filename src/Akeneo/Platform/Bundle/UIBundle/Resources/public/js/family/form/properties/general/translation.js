'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseTranslation = __pimInterop(require('pim/common/properties/translation'));
var SecurityContext = __pimInterop(require('pim/security-context'));

module.exports = BaseTranslation.extend({
  /**
   * {@inheritdoc}
   */
  isReadOnly: function () {
    return !SecurityContext.isGranted('pim_enrich_family_edit_properties');
  },
});
