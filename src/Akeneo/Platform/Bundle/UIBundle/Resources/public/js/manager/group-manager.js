'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));

module.exports = {
  getProductGroups: function (product) {
    var promises = _.map(product.groups, function (groupCode) {
      return FetcherRegistry.getFetcher('group').fetch(groupCode);
    });

    return $.when.apply($, promises).then(function () {
      return _.toArray(arguments);
    });
  },
};
