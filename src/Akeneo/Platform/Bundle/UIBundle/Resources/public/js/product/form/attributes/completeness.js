import 'jquery';
import _ from 'underscore';
import BaseForm from 'pim/form';
import 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import * as toFillFieldProvider from 'pim/provider/to-fill-field-provider';

export default BaseForm.extend({
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
