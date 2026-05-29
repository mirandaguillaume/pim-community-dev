'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));
var Routing = __pimInterop(require('routing'));

module.exports = BaseFetcher.extend({
  activatedLocalesListPromise: null,
  nonFilteredActivatedLocalesListPromise: null,

  /**
   * @param {Object} options
   */
  initialize: function (options) {
    this.options = options || {};
  },

  /**
   * Fetch all activated locales.
   *
   * @return {Promise}
   */
  fetchActivated: function (searchOptions) {
    searchOptions = _.extend({}, searchOptions);
    const nonFiltered = _.has(searchOptions, 'filter_locales') && false === searchOptions.filter_locales;

    let promise = this.activatedLocalesListPromise;
    if (true === nonFiltered) {
      promise = this.nonFilteredActivatedLocalesListPromise;
    }

    if (!promise) {
      if (!_.has(this.options.urls, 'list')) {
        return $.Deferred().reject().promise();
      }

      promise = $.getJSON(
        Routing.generate(this.options.urls.list),
        Object.assign(
          {},
          {
            activated: true,
          },
          searchOptions
        )
      )
        .then(_.identity)
        .promise();

      if (true === nonFiltered) {
        this.nonFilteredActivatedLocalesListPromise = promise;
      } else {
        this.activatedLocalesListPromise = promise;
      }
    }

    return promise;
  },

  /**
   * {inheritdoc}
   */
  clear: function () {
    this.activatedLocalesListPromise = null;
    this.nonFilteredActivatedLocalesListPromise = null;

    BaseFetcher.prototype.clear.apply(this, arguments);
  },
});
