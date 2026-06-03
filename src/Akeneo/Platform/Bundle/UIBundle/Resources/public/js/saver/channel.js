'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseSaver = __pimInterop(require('pim/saver/base'));
var Routing = __pimInterop(require('routing'));
var mediator = __pimInterop(require('oro/mediator'));
var $ = __pimInterop(require('jquery'));

module.exports = _.extend({}, BaseSaver, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (code) {
    if (null === code) {
      return Routing.generate(__moduleConfig.postUrl);
    }

    return Routing.generate(__moduleConfig.putUrl, {code: code});
  },

  /**
   * {@inheritdoc}
   */
  save: function (code, data, method) {
    var queryData = data;
    var locales = [];

    _.each(data.locales, function (locale) {
      locales.push(locale.code);
    });

    queryData.locales = locales;

    return $.ajax({
      type: method,
      url: this.getUrl(code),
      data: JSON.stringify(queryData),
    }).then(
      function (entity) {
        mediator.trigger('pim_enrich:form:entity:post_save', entity);

        return entity;
      }.bind(this)
    );
  },
});
