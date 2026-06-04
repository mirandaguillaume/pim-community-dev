import 'underscore';
import __ from 'oro/translator';
import DeleteAction from 'oro/datagrid/delete-action';
import Dialog from 'pim/dialog';

export default DeleteAction.extend({
  getConfirmDialog: function () {
    const entityCode = this.getEntityCode();

    this.confirmModal = Dialog.confirm(
      __(`pim_enrich.entity.${entityCode}.module.revoke.confirm`),
      __('pim_common.confirm_revocation'),
      this.doDelete.bind(this),
      this.getEntityHint(true)
    );

    return this.confirmModal;
  },
});
