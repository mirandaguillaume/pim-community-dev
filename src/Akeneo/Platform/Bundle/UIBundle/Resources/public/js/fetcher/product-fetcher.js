'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('backbone');
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));
var Routing = __pimInterop(require('routing'));
var mediator = __pimInterop(require('oro/mediator'));
var CacheInvalidator = __pimInterop(require('pim/cache-invalidator'));

module.exports = BaseFetcher.extend({
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
