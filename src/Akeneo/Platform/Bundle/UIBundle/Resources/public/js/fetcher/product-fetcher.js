import $ from 'jquery';
import _ from 'underscore';
import 'backbone';
import BaseFetcher from 'pim/base-fetcher';
import Routing from 'routing';
import mediator from 'oro/mediator';
import CacheInvalidator from 'pim/cache-invalidator';

export default BaseFetcher.extend({
  /**
   * Fetch a product or a product_model based on its id or uuid
   *
   * @param {string} idOrUuid
   *
   * @return {Promise}
   */
  fetch: function (idOrUuid, options = {}) {
    const {silent = false, ...routeParams} = options;

    const params = (idOrUuid + '').match(/^\d+$/) ? {id: idOrUuid} : {uuid: idOrUuid};

    return $.getJSON(Routing.generate(this.options.urls.get, {...routeParams, ...params}))
      .then(function (product) {
        const cacheInvalidator = new CacheInvalidator();
        cacheInvalidator.checkStructureVersion(product);

        if (!silent) {
          mediator.trigger('pim_enrich:form:product:post_fetch', product);
        }

        return product;
      })
      .promise();
  },

  fetchByUuids: function (uuids, options) {
    options = options || {};
    if (0 === uuids.length) {
      return Promise.resolve([]);
    }

    return this.getJSON(this.options.urls.list, _.extend({uuids: uuids.join(',')}, options))
      .then(_.identity)
      .promise();
  },

  /**
   * {@inheritdoc}
   */
  getIdentifierField: function () {
    return $.Deferred().resolve('identifier');
  },
});
