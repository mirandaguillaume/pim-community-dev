'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Label = __pimInterop(require('pim/form/common/label'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = Label.extend({
  /**
   * Provide the object label
   * @return {String}
   */
  getLabel: function () {
    var meta = this.getFormData().meta;

    if (meta && meta.label) {
      return meta.label[UserContext.get('catalogLocale')];
    }

    return this.getFormData().identifier;
  },
});
