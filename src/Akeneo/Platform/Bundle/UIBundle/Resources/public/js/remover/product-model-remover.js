'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseRemover = __pimInterop(require('pim/remover/base'));
var Routing = __pimInterop(require('routing'));

module.exports = _.extend({}, BaseRemover, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (id) {
    return Routing.generate(__moduleConfig.url, {id: id});
  },
});
