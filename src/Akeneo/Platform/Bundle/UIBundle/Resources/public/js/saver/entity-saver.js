import _ from 'underscore';
import BaseSaver from 'pim/saver/base';
import Routing from 'routing';

export default _.extend({}, BaseSaver, {
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
