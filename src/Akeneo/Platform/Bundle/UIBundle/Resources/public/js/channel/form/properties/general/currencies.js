'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/channel/tab/properties/general/currencies'));
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'AknFieldContainer',
  template: _.template(template),

  /**
   * Configures this extension.
   *
   * @return {Promise}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.render.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    FetcherRegistry.getFetcher('currency')
      .fetchAll()
      .then(
        function (currencies) {
          this.$el.html(
            this.template({
              currentCurrencies: this.getFormData().currencies,
              currencies: currencies,
              errors: this.getParent().getValidationErrorsForField('currencies'),
              label: __('pim_enrich.entity.currency.plural_label'),
              requiredLabel: __('pim_common.required_label'),
            })
          );

          this.$('.select2').select2().on('change', this.updateState.bind(this));

          this.renderExtensions();
        }.bind(this)
      );

    return this;
  },

  /**
   * Sets new currencies on change.
   *
   * @param {Object} event
   */
  updateState: function (event) {
    this.setCurrencies(event.val);
  },

  /**
   * Sets specified currencies into root model.
   *
   * @param {Array} codes
   */
  setCurrencies: function (codes) {
    if (null === codes) {
      codes = [];
    }
    var data = this.getFormData();

    data.currencies = codes;
    this.setData(data);
  },
});
