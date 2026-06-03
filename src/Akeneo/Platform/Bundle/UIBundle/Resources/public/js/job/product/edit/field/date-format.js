'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
require('oro/translator');
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var SelectField = __pimInterop(require('pim/job/common/edit/field/select'));

module.exports = SelectField.extend({
  /**
   * {@inherit}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.getFetcher('formats').fetchAll(),
      SelectField.prototype.configure.apply(this, arguments)
    ).then(
      function (formats) {
        this.config.options = formats.date_formats;
      }.bind(this)
    );
  },
});
