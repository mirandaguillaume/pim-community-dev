'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var propertyAccessor = __pimInterop(require('pim/common/property'));
var template = __pimInterop(require('pim/template/form/properties/general'));
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    var config = this.options.config;

    this.$el.html(
      this.template({
        model: this.getFormData(),
        sectionTitle: __(config.sectionTitle),
        codeLabel: __(config.codeLabel),
        formRequired: __(config.formRequired),
        inputField: config.inputField,
        hasId: propertyAccessor.accessProperty(this.getFormData(), 'meta.id') !== null,
      })
    );

    this.$el.find('select.select2').select2({});

    this.renderExtensions();
  },
});
