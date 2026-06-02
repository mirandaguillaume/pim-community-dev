'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var __ = __pimInterop(require('oro/translator'));
var messenger = __pimInterop(require('oro/messenger'));
var BaseModal = __pimInterop(require('pim/form/common/creation/modal'));

module.exports = BaseModal.extend({
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
