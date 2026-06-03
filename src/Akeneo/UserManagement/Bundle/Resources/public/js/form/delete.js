'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var UserRemover = __pimInterop(require('pim/remover/user'));

module.exports = DeleteForm.extend({
  remover: UserRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
