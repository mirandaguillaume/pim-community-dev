import DeleteForm from 'pim/form/common/delete';
import JobInstanceRemover from 'pim/remover/job-instance-export';

export default DeleteForm.extend({
  remover: JobInstanceRemover,
});
