function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var AbstractAction = __pimInterop(require('oro/datagrid/abstract-action'));
('use strict');

module.exports = AbstractAction.extend({
  /** @property oro.PageableCollection */
  collection: undefined,

  /**
   * Initialize action
   *
   * @param {Object} options
   * @param {oro.PageableCollection} options.collection Collection
   * @throws {TypeError} If collection is undefined
   */
  initialize: function (options) {
    options = options || {};

    if (!options.datagrid) {
      throw new TypeError("'datagrid' is required");
    }
    this.collection = options.datagrid.collection;

    AbstractAction.prototype.initialize.apply(this, arguments);
  },

  /**
   * Execute refresh collection
   */
  execute: function () {
    this.datagrid.setAdditionalParameter('refresh', true);
    this.collection.fetch();
    this.datagrid.removeAdditionalParameter('refresh');
  },
});
