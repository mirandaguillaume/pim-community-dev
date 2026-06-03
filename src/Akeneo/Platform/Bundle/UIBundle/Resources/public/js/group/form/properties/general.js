'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var template = __pimInterop(require('pim/template/group/tab/properties/general'));
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        model: this.getFormData(),
        sectionTitle: __('pim_common.general_properties'),
        codeLabel: __('pim_common.code'),
        typeLabel: __('pim_common.type'),
        __: __,
      })
    );

    this.$el.find('select.select2').select2({});

    this.renderExtensions();
  },
});
