function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Backbone = __pimInterop(require('backbone'));
var GridViewsModel = __pimInterop(require('oro/datagrid/grid-views/model'));
('use strict');

module.exports = Backbone.Collection.extend({
  /** @property */
  model: GridViewsModel,
});
