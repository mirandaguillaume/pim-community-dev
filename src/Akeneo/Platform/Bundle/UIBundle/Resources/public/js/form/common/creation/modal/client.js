'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseModal = __pimInterop(require('pim/form/common/creation/modal'));
var messenger = __pimInterop(require('oro/messenger'));
var mediator = __pimInterop(require('oro/mediator'));
var __ = __pimInterop(require('oro/translator'));

module.exports = BaseModal.extend({
  /**
   * {@inheritdoc}
   */
  confirmModal(modal, deferred) {
    this.save().done(() => {
      modal.close();
      modal.remove();
      deferred.resolve();

      messenger.notify('success', __(this.config.successMessage));
      mediator.trigger('datagrid:doRefresh:' + this.config.gridName);
    });
  },
});
