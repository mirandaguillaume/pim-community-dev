import __ from 'oro/translator';
import Dialog from 'pim/dialog';

/**
 * Delete Confirm modal for datagrid
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteConfirm {
  /**
   * Returns a confirm modal
   *
   * @param {string} entityCode
   * @param {any}    callback
   * @param {string} entityHint
   * @return {Promise}
   */
  public static getConfirmDialog(entityCode: string, callback: any, entityHint: string) {
    return Dialog.confirmDelete(
      __(`pim_enrich.entity.${entityCode}.module.delete.confirm`),
      __('pim_common.confirm_deletion'),
      callback,
      entityHint,
      'pim_common.delete'
    );
  }
}

export = DeleteConfirm;
