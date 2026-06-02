'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var ProductRemover = __pimInterop(require('pim/remover/product'));

module.exports = DeleteForm.extend({
  remover: ProductRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
