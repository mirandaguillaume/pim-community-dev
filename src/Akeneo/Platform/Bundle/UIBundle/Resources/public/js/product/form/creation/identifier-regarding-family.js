import Identifier from 'pim/product-edit-form/creation/identifier';
import FetcherRegistry from 'pim/fetcher-registry';

export default Identifier.extend({
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
