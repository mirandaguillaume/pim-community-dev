'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form/common/index/grid'));
var Grid = __pimInterop(require('pim/common/grid'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseForm.extend({
  configure: function () {
    BaseForm.prototype.configure.apply(this, arguments);

    var metaData = this.config.metadata || {};
    // Keep the catalog locale context for the queries used to provide the grid data source
    metaData.localeCode = UserContext.get('catalogLocale');

    // Keep the catalog locale context when the user navigates from the product Edit Form
    metaData.dataLocale = UserContext.get('catalogLocale');

    this.grid = new Grid(this.config.alias, metaData);
  },
});
