import DeleteForm from 'pim/form/common/delete';
import GroupRemover from 'pim/remover/group';

export default DeleteForm.extend({
  remover: GroupRemover,
});
