import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import FetcherRegistry from 'pim/fetcher-registry';
import template from 'pim/template/channel/tab/properties/general/currencies';
import 'jquery.select2';

export default BaseForm.extend({
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
