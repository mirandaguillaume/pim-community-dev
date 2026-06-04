import BaseSave from 'pim/job-instance-edit-form/save';
import JobInstanceSaver from 'pim/saver/job-instance-export';

export default BaseSave.extend({
  /**
   * {@inheritdoc}
   */
  getJobInstanceSaver: function () {
    return JobInstanceSaver;
  },
});
