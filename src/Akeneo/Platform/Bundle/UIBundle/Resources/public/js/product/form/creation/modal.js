import 'underscore';
import __ from 'oro/translator';
import * as messenger from 'oro/messenger';
import BaseModal from 'pim/form/common/creation/modal';

export default BaseModal.extend({
  postSuccess(entity) {
    if (entity.meta?.identifier_generator_warnings) {
      const normalizedWarnings = entity.meta.identifier_generator_warnings.map(warning => {
        return warning.path ? `${warning.path}: ${warning.message} ` : warning.message;
      });

      messenger.notify('warning', __('pim_enrich.entity.product.flash.update.identifier_warning'), normalizedWarnings);
    }
    messenger.notify('success', __(this.config.successMessage));
  },
});
