'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var GroupTypeRemover = __pimInterop(require('pim/remover/group-type'));

module.exports = DeleteForm.extend({
  remover: GroupTypeRemover,
});
