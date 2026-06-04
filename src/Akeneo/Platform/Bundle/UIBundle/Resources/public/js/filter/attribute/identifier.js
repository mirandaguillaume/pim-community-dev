import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseFilter from 'pim/filter/filter';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import template from 'pim/template/filter/product/identifier';
import * as i18n from 'pim/i18n';

export default BaseFilter.extend({
  shortname: 'identifier',
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  isEmpty: function () {
    return _.isEmpty(this.getValue());
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function () {
    return this.template({
      __: __,
      value: _.isArray(this.getValue()) ? this.getValue().join(', ') : '',
      field: this.getField(),
      isEditable: this.isEditable(),
    });
  },

  /**
   * {@inheritdoc}
   */
  getTemplateContext: function () {
    if (this.getCode() === 'identifier') {
      // it means it's a product model
      return $.when(BaseFilter.prototype.getTemplateContext.apply(this, arguments)).then(
        function (templateContext) {
          return _.extend({}, templateContext, {
            removable: false,
          });
        }.bind(this)
      );
    } else {
      return $.when(
        BaseFilter.prototype.getTemplateContext.apply(this, arguments),
        FetcherRegistry.getFetcher('attribute').fetch(this.getCode())
      ).then(
        function (templateContext, attribute) {
          return _.extend({}, templateContext, {
            label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
            removable: false,
          });
        }.bind(this)
      );
    }
  },

  /**
   * {@inheritdoc}
   */
  updateState: function () {
    var value = this.$('[name="filter-value"]')
      .val()
      .split(/[\s,]+/);
    var cleanedValues = _.reject(value, function (val) {
      return '' === val;
    });

    this.setData({
      field: this.getField(),
      operator: 'IN',
      value: cleanedValues,
    });
  },
});
