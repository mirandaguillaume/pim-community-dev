import $ from 'jquery';
import _ from 'underscore';
import AddAttributeSelect from 'pim/product/add-select/attribute';
import FetcherRegistry from 'pim/fetcher-registry';
import analytics from 'pim/analytics';

export default AddAttributeSelect.extend({
  /**
   * {@inheritdoc}
   */
  fetchItems: function (searchParameters) {
    return this.getItemsToExclude().then(
      function (attributeCodes) {
        searchParameters.options.excluded_identifiers = attributeCodes;

        return FetcherRegistry.getFetcher(this.mainFetcher)
          .search(searchParameters)
          .then(
            function (attributes) {
              const groupCodes = _.unique(_.pluck(attributes, 'group'));

              return FetcherRegistry.getFetcher('attribute-group')
                .fetchByIdentifiers(groupCodes)
                .then(
                  function (attributeGroups) {
                    return this.populateGroupProperties(attributes, attributeGroups);
                  }.bind(this)
                );
            }.bind(this)
          );
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  getItemsToExclude: function () {
    return $.Deferred().resolve(
      this.getFormData().attributes.map(attribute => {
        return attribute.code;
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  addItems: function () {
    this.getRoot().trigger(this.addEvent, {codes: this.selection});

    analytics.appcuesTrack('family-grid:mass-edit:attributes-added', {
      codes: this.selection,
    });
  },

  /**
   * {@inheritdoc}
   */
  getSelectSearchParameters: function () {
    return _.extend({}, AddAttributeSelect.prototype.getSelectSearchParameters.apply(this, arguments), {
      rights: 0,
    });
  },
});
