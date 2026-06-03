'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var ProductModelRemover = __pimInterop(require('pim/remover/product-model'));

module.exports = DeleteForm.extend({
  remover: ProductModelRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
