'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/channel/tab/properties/conversion-unit'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var {Helper} = __pimInterop(require('akeneo-design-system'));
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),
  locale: UserContext.get('uiLocale'),
  config: null,

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    $.when(
      FetcherRegistry.getFetcher('attribute').search({
        types: 'pim_catalog_metric',
        options: {limit: 1000},
      }),
      FetcherRegistry.getFetcher('measure').fetchAll()
    ).then(
      function (attributes, measures) {
        this.$el.html(
          this.template({
            conversionUnits: this.getFormData().conversion_units,
            metrics: attributes,
            measures,
            locale: this.locale,
            label: __(this.config.label),
            fieldBaseId: this.config.fieldBaseId,
            doNotConvertLabel: __('pim_enrich.entity.channel.property.do_not_convert'),
            i18n,
            __,
          })
        );

        this.renderReact(
          Helper,
          {children: __('pim_enrich.entity.channel.property.label_conversion_units')},
          this.$('.tabsection-helper').get(0)
        );

        this.$('.select2').select2().on('change', this.updateState.bind(this));
        this.renderExtensions();
      }.bind(this)
    );

    return this;
  },

  /**
   * Sets new attribute conversion unit on change.
   *
   * @param {Object} event
   */
  updateState: function (event) {
    this.setAttributeConversionUnit(
      event.currentTarget.id.replace(this.config.fieldBaseId, ''),
      event.currentTarget.value
    );
  },

  /**
   * Sets specified conversion unit settings into form model.
   *
   * @param {String} attribute
   * @param {String} value
   */
  setAttributeConversionUnit: function (attribute, value) {
    var data = this.getFormData();

    if (_.isEmpty(data.conversion_units)) {
      data.conversion_units = {};
    }

    if (value !== 'no_conversion') {
      data.conversion_units[attribute] = value;
    } else {
      delete data.conversion_units[attribute];
    }

    this.setData(data);
  },
});
