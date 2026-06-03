'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var AssociationTypeRemover = __pimInterop(require('pim/remover/association-type'));

module.exports = DeleteForm.extend({
  remover: AssociationTypeRemover,
});
