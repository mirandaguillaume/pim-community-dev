'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Routing = __pimInterop(require('routing'));
var Attributes = __pimInterop(require('pim/form/common/attributes'));
require('jquery');

module.exports = Attributes.extend({
  /**
   * {@inheritdoc}
   */
  generateRemoveAttributeUrl: function (attribute) {
    if ((this.getFormData().meta.id + '').match(/^\d+$/)) {
      return Routing.generate(this.config.removeAttributeRoute, {
        id: this.getFormData().meta.id,
        attributeId: attribute.meta.id,
      });
    }

    return Routing.generate(this.config.removeAttributeRoute, {
      uuid: this.getFormData().meta.id,
      attributeId: attribute.meta.id,
    });
  },
});
