function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var __ = __pimInterop(require('oro/translator'));
var DeleteAction = __pimInterop(require('oro/datagrid/delete-action'));
var Dialog = __pimInterop(require('pim/dialog'));
('use strict');

module.exports = DeleteAction.extend({
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
