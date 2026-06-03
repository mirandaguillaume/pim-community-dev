'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseRemover = __pimInterop(require('pim/remover/base'));
var Routing = __pimInterop(require('routing'));

module.exports = _.extend({}, BaseRemover, {
  /**
   * Gets url in configuration for remover module
   *
   * @param {String} code Code for group type entity
   *
   * {@inheritdoc}
   */
  getUrl: function (code) {
    return Routing.generate(__moduleConfig.url, {code: code});
  },
});
