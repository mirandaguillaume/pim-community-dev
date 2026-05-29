'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));

module.exports = BaseFetcher.extend({
  /**
   * Fetch only the parent category tree
   * User right will not be apply.
   * @return {Promise}
   */
  fetchCategoryTree: function () {
    return this.getJSON(this.options.urls.list_channel_category_tree).then(_.identity).promise();
  },
});
