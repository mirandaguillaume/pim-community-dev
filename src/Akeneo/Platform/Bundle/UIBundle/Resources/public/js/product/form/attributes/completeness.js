'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var UserContext = __pimInterop(require('pim/user-context'));
var toFillFieldProvider = __pimInterop(require('pim/provider/to-fill-field-provider'));

module.exports = BaseForm.extend({
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritDoc}
   */
  addFieldExtension: function (event) {
    const scope = UserContext.get('catalogScope');
    const locale = UserContext.get('catalogLocale');
    const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(this.getFormData(), scope, locale);
    const field = event.field;

    if (_.contains(fieldsToFill, field.attribute.code)) {
      field.addElement('badge', 'completeness', '<span class="AknBadge AknBadge--small AknBadge--highlight"></span>');
    }
  },
});
