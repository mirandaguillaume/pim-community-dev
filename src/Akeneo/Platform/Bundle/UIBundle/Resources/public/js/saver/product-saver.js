'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseSaver = __pimInterop(require('pim/saver/base'));
var Routing = __pimInterop(require('routing'));

module.exports = _.extend({}, BaseSaver, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (uuid) {
    return Routing.generate(__moduleConfig.url, {uuid});
  },
});
