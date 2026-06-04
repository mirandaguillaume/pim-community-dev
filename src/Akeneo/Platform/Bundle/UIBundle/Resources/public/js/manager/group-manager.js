import $ from 'jquery';
import _ from 'underscore';
import FetcherRegistry from 'pim/fetcher-registry';

export default {
  getProductGroups: function (product) {
    var promises = _.map(product.groups, function (groupCode) {
      return FetcherRegistry.getFetcher('group').fetch(groupCode);
    });

    return $.when.apply($, promises).then(function () {
      return _.toArray(arguments);
    });
  },
};
