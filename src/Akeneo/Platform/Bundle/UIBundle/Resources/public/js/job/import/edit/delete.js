'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var DeleteForm = __pimInterop(require('pim/form/common/delete'));
var JobInstanceRemover = __pimInterop(require('pim/remover/job-instance-import'));

module.exports = DeleteForm.extend({
  remover: JobInstanceRemover,
});
