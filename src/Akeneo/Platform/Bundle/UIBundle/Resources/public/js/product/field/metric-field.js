'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Field = __pimInterop(require('pim/field'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var fieldTemplate = __pimInterop(require('pim/template/product/field/metric'));
var initSelect2 = __pimInterop(require('pim/initselect2'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first .data, .field-input:first .unit': 'updateModel',
  },
  renderInput: function (context) {
    const $element = $(this.fieldTemplate(_.extend({}, context, {__: __})));
    initSelect2.init($element.find('.unit'));

    return $element;
  },
  getTemplateContext: function () {
    return $.when(
      Field.prototype.getTemplateContext.apply(this, arguments),
      FetcherRegistry.getFetcher('measure').fetchAll()
    ).then(function (templateContext, measures) {
      const measurementFamily = measures.find(family => family.code === templateContext.attribute.metric_family);
      templateContext.i18n = i18n;
      templateContext.units = measurementFamily.units;
      templateContext.uiLocale = UserContext.get('uiLocale');

      return templateContext;
    });
  },
  setFocus: function () {
    this.$('.data:first').focus();
  },
  updateModel: function () {
    var amount = this.$('.field-input:first .data').val();
    var unit = this.$('.field-input:first .unit').select2('val');

    this.setCurrentValue({
      unit: '' !== unit ? unit : this.attribute.default_metric_unit,
      amount: '' !== amount ? amount : null,
    });
  },
});
