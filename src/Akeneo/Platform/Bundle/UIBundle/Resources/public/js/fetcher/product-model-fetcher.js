'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var ProductFetcher = __pimInterop(require('pim/product-fetcher'));
var UserContext = __pimInterop(require('pim/user-context'));
require('oro/mediator');
var Routing = __pimInterop(require('routing'));

module.exports = ProductFetcher.extend({
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    this.options = options || {};

    ProductFetcher.prototype.initialize.apply(this, [options]);
  },

  /**
   * {@inheritdoc}
   */
  getIdentifierField: function () {
    return $.Deferred().resolve('code');
  },

  /**
   * Fetch all children of the given parent.
   *
   * @return {Promise}
   */
  fetchChildren: function (parentId) {
    if (!_.has(this.options.urls, 'children')) {
      return $.Deferred().reject().promise();
    }

    return $.getJSON(Routing.generate(this.options.urls.children), {
      id: parentId,
      scope: UserContext.get('catalogScope'),
      locale: UserContext.get('catalogLocale'),
    })
      .then(_.identity)
      .promise();
  },
});
