'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseSave = __pimInterop(require('pim/job-instance-edit-form/save'));
var JobInstanceSaver = __pimInterop(require('pim/saver/job-instance-import'));

module.exports = BaseSave.extend({
  /**
   * {@inheritdoc}
   */
  getJobInstanceSaver: function () {
    return JobInstanceSaver;
  },
});
