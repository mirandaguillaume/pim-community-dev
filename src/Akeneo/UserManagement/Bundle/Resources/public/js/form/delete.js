import DeleteForm from 'pim/form/common/delete';
import UserRemover from 'pim/remover/user';

export default DeleteForm.extend({
  remover: UserRemover,

  /**
   * {@inheritdoc}
   */
  getIdentifier: function () {
    return this.getFormData().meta.id;
  },
});
