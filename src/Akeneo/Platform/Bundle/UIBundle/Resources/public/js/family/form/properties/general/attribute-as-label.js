'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var SecurityContext = __pimInterop(require('pim/security-context'));
var template = __pimInterop(require('pim/template/family/tab/general/attribute-as-label'));
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'AknFieldContainer',
  template: _.template(template),
  errors: [],
  catalogLocale: UserContext.get('catalogLocale'),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(
      this.template({
        i18n: i18n,
        catalogLocale: this.catalogLocale,
        attributes: _.filter(this.getFormData().attributes, function (attribute) {
          return attribute.type === 'pim_catalog_text' || attribute.type === 'pim_catalog_identifier';
        }),
        currentAttribute: this.getFormData().attribute_as_label,
        fieldBaseId: this.config.fieldBaseId,
        errors: this.errors,
        label: __(this.config.label),
        requiredLabel: __('pim_common.required_label'),
        isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties'),
      })
    );

    this.$('.select2').select2().on('change', this.updateState.bind(this));

    this.renderExtensions();
  },

  /**
   * Update object state on property change
   *
   * @param event
   */
  updateState: function (event) {
    var data = this.getFormData();
    data.attribute_as_label = event.currentTarget.value;
    this.setData(data);
  },
});
