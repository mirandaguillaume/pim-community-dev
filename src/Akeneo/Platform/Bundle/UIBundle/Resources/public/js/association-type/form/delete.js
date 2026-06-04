import DeleteForm from 'pim/form/common/delete';
import AssociationTypeRemover from 'pim/remover/association-type';

export default DeleteForm.extend({
  remover: AssociationTypeRemover,
});
