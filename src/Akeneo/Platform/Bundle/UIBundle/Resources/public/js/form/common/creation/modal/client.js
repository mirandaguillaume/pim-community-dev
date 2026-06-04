import BaseModal from 'pim/form/common/creation/modal';
import messenger from 'oro/messenger';
import mediator from 'oro/mediator';
import __ from 'oro/translator';

export default BaseModal.extend({
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
