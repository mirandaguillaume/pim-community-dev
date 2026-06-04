import DeleteForm from 'pim/form/common/delete';
import ProductModelRemover from 'pim/remover/product-model';

export default DeleteForm.extend({
  remover: ProductModelRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
