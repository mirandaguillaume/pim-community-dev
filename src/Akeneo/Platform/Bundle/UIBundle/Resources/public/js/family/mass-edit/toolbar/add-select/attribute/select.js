import 'jquery';
import _ from 'underscore';
import FetcherRegistry from 'pim/fetcher-registry';
import FamilyAddAttributeSelect from 'pim/family-edit-form/attributes/toolbar/add-select/attribute';

export default FamilyAddAttributeSelect.extend({
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
