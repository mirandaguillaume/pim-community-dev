import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseField from 'pim/form/common/fields/field';
import fetcherRegistry from 'pim/fetcher-registry';
import template from 'pim/template/form/common/fields/select';
import UserContext from 'pim/user-context';
import i18n from 'pim/i18n';

export default BaseField.extend({
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
