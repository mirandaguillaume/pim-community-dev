'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Routing = __pimInterop(require('routing'));
var contextData = {};

module.exports = {
  /**
   * Fetches data from the back then stores it.
   *
   * @returns {Promise}
   */
  initialize: () => {
    return $.get(Routing.generate('pim_localization_format_date')).then(response => (contextData = response));
  },

  /**
   * Returns the value corresponding to the specified key.
   *
   * @param {String} key
   *
   * @returns {*}
   */
  get: key => contextData[key],
};
