'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));

module.exports = BaseFetcher.extend({
  /**
   * Fetch attributes available as axes for the given family
   *
   * @param {String} familyCode
   *
   * @return {Promise}
   */
  fetchAvailableAxes: function (familyCode) {
    return this.getJSON(this.options.urls.available_axes, {code: familyCode}).then(_.identity).promise();
  },
});
