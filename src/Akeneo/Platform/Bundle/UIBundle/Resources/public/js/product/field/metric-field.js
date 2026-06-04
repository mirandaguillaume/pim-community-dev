import $ from 'jquery';
import Field from 'pim/field';
import _ from 'underscore';
import __ from 'oro/translator';
import FetcherRegistry from 'pim/fetcher-registry';
import fieldTemplate from 'pim/template/product/field/metric';
import initSelect2 from 'pim/initselect2';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';

export default Field.extend({
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
