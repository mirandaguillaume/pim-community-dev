'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
var AddAttributeSelect = __pimInterop(require('pim/product/add-select/attribute'));

module.exports = AddAttributeSelect.extend({
  /**
   * {@inheritdoc}
   */
  getItemsToExclude: function () {
    return $.Deferred().resolve(this.getParent().getCurrentFilters());
  },
});
