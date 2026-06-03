'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Routing = __pimInterop(require('routing'));
var BaseFetcher = __pimInterop(require('pim/base-fetcher'));

module.exports = BaseFetcher.extend({
  /**
   * Fetch default columns for grid with given alias
   *
   * @param {string} alias
   *
   * @return Promise
   */
  defaultColumns: function (alias) {
    let columns = this.entityPromises['columns'];

    if (!columns) {
      columns = $.getJSON(Routing.generate(this.options.urls.columns, {alias: alias}));
      this.entityPromises['columns'] = columns;
    }

    return columns;
  },

  /**
   * Fetch default datagrid view for given alias of the current user
   *
   * @param {string} alias
   *
   * @return Promise
   */
  defaultUserView: function (alias) {
    let view = this.entityPromises['view'];

    if (!view) {
      view = $.getJSON(Routing.generate(this.options.urls.userDefaultView, {alias: alias}));
      this.entityPromises['view'] = view;
    }

    return view;
  },
});
