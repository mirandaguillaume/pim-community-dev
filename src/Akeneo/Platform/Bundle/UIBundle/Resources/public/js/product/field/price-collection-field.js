'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var Field = __pimInterop(require('pim/field'));
var _ = __pimInterop(require('underscore'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var fieldTemplate = __pimInterop(require('pim/template/product/field/price-collection'));

module.exports = Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first input[type="text"]': 'updateModel',
  },
  renderInput: function (context) {
    if (undefined === context.value) {
      return null;
    }

    context.value.data = _.sortBy(context.value.data, 'currency');

    return this.fieldTemplate(context);
  },
  updateModel: function () {
    var prices = [];
    var inputs = this.$('.field-input:first .price-input input');
    _.each(
      inputs,
      function (input) {
        var $input = $(input);
        var inputData = $input.val();
        prices.push({
          amount: '' === inputData ? null : inputData,
          currency: $input.data('currency'),
        });
      }.bind(this)
    );

    this.setCurrentValue(_.sortBy(prices, 'currency'));
  },
  getTemplateContext: function () {
    return $.when(
      Field.prototype.getTemplateContext.apply(this, arguments),
      FetcherRegistry.getFetcher('currency').fetchAll()
    ).then(function (templateContext, currencies) {
      templateContext.currencies = currencies;

      return templateContext;
    });
  },
  setFocus: function () {
    this.$('input[type="text"]:first').focus();
  },
});
