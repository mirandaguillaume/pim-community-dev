import DeleteForm from 'pim/form/common/delete';
import ProductRemover from 'pim/remover/product';

export default DeleteForm.extend({
  remover: ProductRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
