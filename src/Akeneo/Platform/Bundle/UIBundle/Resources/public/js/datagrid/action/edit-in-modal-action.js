'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var AbstractAction = __pimInterop(require('oro/datagrid/abstract-action'));
var formModalCreator = __pimInterop(require('pim/common/form-modal-creator'));

module.exports = AbstractAction.extend({
  /**
   * {@inheritdoc}
   */
  execute: function () {
    return formModalCreator.createModal(this.model.get(this.propertyCode), this.fetcher);
  },
});
