'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var attributeTemplate = __pimInterop(require('pim/template/form/tab/attributes'));

module.exports = BaseForm.extend({
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
