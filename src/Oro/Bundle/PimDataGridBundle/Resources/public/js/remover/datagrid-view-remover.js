'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Routing = __pimInterop(require('routing'));

module.exports = {
  /**
   * Remove the given datagridView.
   * Return the DELETE request promise.
   *
   * @param {object} datagridView
   *
   * @returns {Promise}
   */
  remove: function (datagridView) {
    var removeRoute = Routing.generate(__moduleConfig.url, {identifier: datagridView.id});

    return $.ajax({url: removeRoute, type: 'DELETE'});
  },
};
