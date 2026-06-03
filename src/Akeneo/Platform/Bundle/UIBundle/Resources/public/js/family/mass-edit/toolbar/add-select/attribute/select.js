'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var FamilyAddAttributeSelect = __pimInterop(require('pim/family-edit-form/attributes/toolbar/add-select/attribute'));

module.exports = FamilyAddAttributeSelect.extend({
  fetchAttributeGroups(attributes) {
    const groupCodes = _.unique(_.pluck(attributes, 'group'));

    return FetcherRegistry.getFetcher('attribute-group')
      .fetchByIdentifiers(groupCodes)
      .then(attributeGroups => {
        return this.populateGroupProperties(attributes, attributeGroups);
      });
  },

  /**
   * {@inheritdoc}
   */
  fetchItems: function (searchParameters) {
    return this.getItemsToExclude().then(identifiersToExclude => {
      searchParameters.options.excluded_identifiers = identifiersToExclude;

      return FetcherRegistry.getFetcher(this.mainFetcher)
        .search(searchParameters)
        .then(attributes => {
          return this.fetchAttributeGroups(attributes);
        });
    });
  },
});
