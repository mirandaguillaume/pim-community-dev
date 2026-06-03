'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Routing = __pimInterop(require('routing'));

module.exports = {
  /**
   * Save the given datagridView for the given gridAlias.
   * Return the POST request promise.
   *
   * @param {object} datagridView
   * @param {string} gridAlias
   *
   * @returns {Promise}
   */
  save: function (datagridView, gridAlias) {
    var saveRoute = Routing.generate(__moduleConfig.url, {alias: gridAlias});

    return $.post(saveRoute, {view: datagridView});
  },
};
