import DeleteForm from 'pim/form/common/delete';
import GroupTypeRemover from 'pim/remover/group-type';

export default DeleteForm.extend({
  remover: GroupTypeRemover,
});
