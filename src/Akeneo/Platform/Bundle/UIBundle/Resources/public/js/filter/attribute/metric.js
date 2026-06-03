'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/attribute/attribute'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var template = __pimInterop(require('pim/template/filter/attribute/metric'));
require('jquery.select2');

module.exports = BaseFilter.extend({
  shortname: 'metric',
  template: _.template(template),
  events: {
    'change [name="filter-data"], [name="filter-operator"], select.unit': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.getFetcher('attribute').fetch(this.getCode()),
      BaseFilter.prototype.configure.apply(this, arguments)
    ).then(
      function (attribute) {
        this.listenTo(
          this.getRoot(),
          'pim_enrich:form:entity:pre_update',
          function (data) {
            _.defaults(data, {
              field: this.getCode(),
              operator: _.first(_.values(this.config.operators)),
              value: {
                amount: '',
                unit: attribute.default_metric_unit,
              },
            });
          }.bind(this)
        );
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  isEmpty: function () {
    return (
      !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
      (undefined === this.getValue() || undefined === this.getValue().amount || '' === this.getValue().amount)
    );
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(
      _.extend({}, templateContext, {
        __: __,
        value: this.getValue(),
        field: this.getField(),
        operator: this.getOperator(),
        operators: this.getLabelledOperatorChoices(this.shortname),
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.operator, .unit').select2({minimumResultsForSearch: -1});
  },

  /**
   * {@inheritdoc}
   */
  getTemplateContext: function () {
    return $.when(
      BaseFilter.prototype.getTemplateContext.apply(this, arguments),
      FetcherRegistry.getFetcher('measure').fetchAll()
    ).then(
      function (templateContext, measures) {
        const measurementFamily = measures.find(family => family.code === templateContext.attribute.metric_family);

        return _.extend({}, templateContext, {
          units: measurementFamily.units,
          i18n,
          locale: UserContext.get('uiLocale'),
        });
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  updateState: function () {
    var value = {
      amount: this.$('[name="filter-data"]').val(),
      unit: this.$('select[name="filter-unit"]').val(),
    };

    var operator = this.$('[name="filter-operator"]').val();

    this.setData({
      field: this.getField(),
      operator: operator,
      value: value,
    });
  },
});
