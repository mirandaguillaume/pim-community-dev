import 'jquery';
import _ from 'underscore';
import BaseForm from 'pim/form';
import attributeTemplate from 'pim/template/form/tab/attributes';

export default BaseForm.extend({
  template: _.template(attributeTemplate),
  readOnly: false,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:update_read_only',
      function (readOnly) {
        this.readOnly = readOnly;
      }.bind(this)
    );

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritDoc}
   */
  addFieldExtension: function (event) {
    if (!this.isAttributeEditable()) {
      event.field.setEditable(false);
    }
  },

  /**
   * Is the current attribute editable ?
   *
   * @return {Boolean}
   */
  isAttributeEditable: function () {
    return !this.readOnly;
  },
});
