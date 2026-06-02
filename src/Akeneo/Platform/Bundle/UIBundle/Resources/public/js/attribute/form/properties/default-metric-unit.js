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
var __ = __pimInterop(require('oro/translator'));
var BaseField = __pimInterop(require('pim/form/common/fields/field'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/form/common/fields/select'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseField.extend({
  events: {
    'change select': function (event) {
      this.errors = [];
      this.updateModel(this.getFieldValue(event.target));
      this.getRoot().render();
    },
  },
  template: _.template(template),
  measures: {},

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseField.prototype.configure.apply(this, arguments),
      fetcherRegistry
        .getFetcher('measure')
        .fetchAll()
        .then(
          function (measures) {
            this.measures = measures;
          }.bind(this)
        )
    );
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    const measurementFamily = this.measures.find(family => family.code === this.getFormData().metric_family);

    return this.template(
      _.extend(templateContext, {
        value: this.getFormData()[this.fieldName],
        choices: this.formatChoices(measurementFamily.units),
        multiple: false,
        labels: {
          defaultLabel: __('pim_enrich.entity.attribute.property.default_metric_unit.choose'),
        },
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('select.select2').select2();
  },

  /**
   * {@inheritdoc}
   *
   * This field shouldn't be displayed if the attribute has no metric family defined yet.
   */
  isVisible: function () {
    return undefined !== this.getFormData().metric_family && null !== this.getFormData().metric_family;
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
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val();
  },
});
