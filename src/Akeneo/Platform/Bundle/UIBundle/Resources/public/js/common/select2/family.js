'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var Routing = __pimInterop(require('routing'));

module.exports = {
  /**
   * Provide the config for a family select2 field
   *
   * @param {string} initialValue
   * @return {object}
   */
  getConfig: function (initialValue) {
    return {
      allowClear: true,
      ajax: {
        url: Routing.generate('pim_enrich_family_rest_index'),
        quietMillis: 250,
        cache: true,
        data: function (term, page) {
          return {
            search: term,
            options: {
              limit: 20,
              page: page,
              locale: UserContext.get('catalogLocale'),
            },
          };
        },
        results: function (families) {
          var data = {
            more: 20 === _.keys(families).length,
            results: [],
          };
          _.each(families, function (value, key) {
            data.results.push({
              id: key,
              text: i18n.getLabel(value.labels, UserContext.get('catalogLocale'), value.code),
            });
          });

          return data;
        },
      },
      initSelection: function (element, callback) {
        if (null !== initialValue) {
          FetcherRegistry.getFetcher('family')
            .fetch(initialValue)
            .then(function (family) {
              callback({
                id: family.code,
                text: i18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code),
              });
            });
        }
      },
    };
  },
};
