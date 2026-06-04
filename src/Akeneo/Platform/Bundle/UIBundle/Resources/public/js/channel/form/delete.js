import DeleteForm from 'pim/form/common/delete';
import ChannelRemover from 'pim/remover/channel';

export default DeleteForm.extend({
  remover: ChannelRemover,
});
