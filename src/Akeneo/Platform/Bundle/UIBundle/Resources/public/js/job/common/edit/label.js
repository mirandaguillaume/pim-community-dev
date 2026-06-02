'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseLabel = __pimInterop(require('pim/form/common/label'));
var __ = __pimInterop(require('oro/translator'));

module.exports = BaseLabel.extend({
  /**
   * Provide the object label
   *
   * @return {String}
   */
  getLabel: function () {
    // The key is for example 'pim_import_export.entity.import_profile.uppercase_label'
    const prefix = __('pim_import_export.entity.' + this.getFormData().type + '_profile.uppercase_label');

    return prefix + ' - ' + this.getFormData().label;
  },
});
