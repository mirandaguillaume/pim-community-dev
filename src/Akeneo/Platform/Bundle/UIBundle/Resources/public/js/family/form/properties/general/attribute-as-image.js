'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var SecurityContext = __pimInterop(require('pim/security-context'));
var template = __pimInterop(require('pim/template/family/tab/general/attribute-as-image'));
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
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.getAvailableAttributes().then(
      function (attributes) {
        this.$el.html(
          this.template({
            i18n: i18n,
            catalogLocale: this.catalogLocale,
            attributes: attributes,
            currentAttribute: this.getFormData().attribute_as_image,
            fieldBaseId: this.config.fieldBaseId,
            errors: this.errors,
            label: __(this.config.label),
            emptyLabel: __(this.config.emptyLabel),
            isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties'),
          })
        );

        this.$('.select2').select2().on('change', this.updateState.bind(this));

        this.renderExtensions();
      }.bind(this)
    );
  },

  /**
   * Update object state on property change
   *
   * @param event
   */
  updateState: function (event) {
    let data = this.getFormData();
    const value = event.currentTarget.value;
    data.attribute_as_image = 'no_attribute_as_image' === value ? null : event.currentTarget.value;
    this.setData(data);
  },

  /**
   * Returns the list of available attributes for this extension:
   * - Should belong to the family
   * - Should be a valid attribute type
   * - Should not be neither localizable nor scopable
   *
   * @returns {Promise}
   */
  getAvailableAttributes: function () {
    const imageAttributes = this.getFormData().attributes.filter(attribute => {
      return this.config.validAttributeTypes.includes(attribute.type);
    });

    const imageAttributeCodes = _.pluck(imageAttributes, 'code');

    return FetcherRegistry.getFetcher('attribute')
      .fetchByIdentifiers(imageAttributeCodes)
      .then(function (attributes) {
        return _.where(attributes, {scopable: false, localizable: false});
      });
  },
});
