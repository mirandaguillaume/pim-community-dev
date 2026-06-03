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
  getUrl: function (identifier) {
    if (this.identifierProperty !== undefined) {
      return Routing.generate(this.url, {[this.identifierProperty]: identifier});
    }

    return Routing.generate(this.url, {identifier: identifier});
  },

  /**
   * Sets the url
   *
   * @param {String} url Route url
   */
  setUrl: function (url) {
    this.url = url;

    return this;
  },

  /**
   * Sets the identifierProperty for the url
   *
   * @param {string} identifierProperty
   */
  setIdentifierProperty: function (identifierProperty) {
    this.identifierProperty = identifierProperty;

    return this;
  },
});
