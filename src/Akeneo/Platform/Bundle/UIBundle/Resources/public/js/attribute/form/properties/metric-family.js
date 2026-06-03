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
    return this.template(
      _.extend(templateContext, {
        value: this.getFormData()[this.fieldName],
        choices: this.formatChoices(this.measures),
        multiple: false,
        labels: {
          defaultLabel: __('pim_enrich.entity.attribute.property.metric_family.choose'),
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
   * Transforms:
   *
   * {
   *     Area: {...},
   *     Binary: {...}
   * }
   *
   * into:
   *
   * {
   *     Area: "Surface",
   *     Binary: "Binaire"
   * }
   *
   * (for locale fr_FR)
   *
   * @param {Object} measures
   */
  formatChoices: function (measures) {
    const choices = {};
    const locale = UserContext.get('uiLocale');
    measures.forEach(family => (choices[family.code] = i18n.getLabel(family.labels, locale, family.code)));

    return choices;
  },

  /**
   * {@inheritdoc}
   *
   * Override to reset the default metric unit each time the metric family changes.
   */
  updateModel: function () {
    BaseField.prototype.updateModel.apply(this, arguments);

    this.setData({default_metric_unit: null}, {silent: true});
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val();
  },
});
