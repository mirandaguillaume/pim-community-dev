import _ from 'underscore';
import 'oro/translator';
import BaseForm from 'pim/form';
import fetcherRegistry from 'pim/fetcher-registry';

export default BaseForm.extend({
  /**
   * {@inherit}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:filter:set-default', this.addFilter.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Adds filters to the collection.
   *
   * @param {Object} event
   */
  addFilter: function (event) {
    event.push(
      fetcherRegistry
        .getFetcher('attribute')
        .fetchByTypes(this.config.types)
        .then(function (attributes) {
          return _.pluck(attributes, 'code');
        })
    );
  },
});
