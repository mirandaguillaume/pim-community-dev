import 'underscore';
import AbstractAction from 'oro/datagrid/abstract-action';
import formModalCreator from 'pim/common/form-modal-creator';

export default AbstractAction.extend({
  /**
   * {@inheritdoc}
   */
  execute: function () {
    return formModalCreator.createModal(this.model.get(this.propertyCode), this.fetcher);
  },
});
