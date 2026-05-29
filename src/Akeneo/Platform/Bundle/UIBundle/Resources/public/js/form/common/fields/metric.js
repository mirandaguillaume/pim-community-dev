/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var BaseField = __pimInterop(require('pim/form/common/fields/field'));
require('oro/translator');
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/form/common/fields/metric'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseField.extend({
  events: {
    'keyup input': function () {
      this.errors = [];
      this.updateModel(this.getValue());
    },
    'change select': function () {
      this.errors = [];
      this.updateModel(this.getValue());
      this.getRoot().render();
    },
  },
  template: _.template(template),
  metricFamily: null,
  defaultMetricUnit: null,

  /**
   * {@inheritdoc}
   */
  initialize() {
    this.metricFamily = null;
    this.defaultMetricUnit = null;

    return BaseField.prototype.initialize.apply(this, arguments);
  },

  /**
   * @param {String} metricFamily
   */
  setMetricFamily(metricFamily) {
    this.metricFamily = metricFamily;
  },

  /**
   * @param {String} defaultMetricUnit
   */
  setDefaultMetricUnit(defaultMetricUnit) {
    this.defaultMetricUnit = defaultMetricUnit;
  },

  /**
   * {@inheritdoc}
   */
  renderInput(templateContext) {
    return this.template(
      _.extend(templateContext, {
        value: {
          amount: this.getModelValuePart('amount'),
          unit: this.getModelValuePart('unit'),
        },
      })
    );
  },

  /**
   * @param {String} name
   *
   * @returns {*|null}
   */
  getModelValuePart(name) {
    return undefined !== this.getModelValue() ? this.getModelValue()[name] : null;
  },

  /**
   * {@inheritdoc}
   */
  getTemplateContext: function () {
    return $.when(
      BaseField.prototype.getTemplateContext.apply(this, arguments),
      FetcherRegistry.getFetcher('measure').fetchAll()
    ).then((parentContext, measures) => {
      const measurementFamily = measures.find(family => family.code === this.metricFamily);

      return Object.assign({}, parentContext, {
        unitChoices: this.formatChoices(measurementFamily.units),
        defaultUnit: this.defaultMetricUnit,
      });
    });
  },

  /**
   * {@inheritdoc}
   */
  postRender() {
    this.$('select.select2').select2({allowClear: true});
  },

  /**
   * Transforms:
   *
   * {
   *     BIT: {...},
   *     BYTE: {...}
   * }
   *
   * into:
   *
   * {
   *     BIT: "Bit",
   *     BYTE: "Octet"
   * }
   *
   * (for locale fr_FR)
   *
   * @param {Object} units
   */
  formatChoices: function (units) {
    const choices = {};
    const locale = UserContext.get('uiLocale');
    units.forEach(unit => (choices[unit.code] = i18n.getLabel(unit.labels, locale, unit.code)));

    return choices;
  },

  /**
   * @return {Object}
   */
  getValue() {
    return {
      amount: this.$('.amount').val(),
      unit: this.$('.unit').val(),
    };
  },
});
