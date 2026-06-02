'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Identifier = __pimInterop(require('pim/product-edit-form/creation/identifier'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));

module.exports = Identifier.extend({
  shouldDisplay: async function () {
    const familyCode = this.getFormData()?.family;
    if (familyCode) {
      return FetcherRegistry.getFetcher('family')
        .fetch(familyCode)
        .then(family => {
          return !!family.attributes.find(attribute => attribute.is_main_identifier);
        });
    } else {
      return new Promise(resolve => resolve(false));
    }
  },
});
