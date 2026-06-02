'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/filter'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var template = __pimInterop(require('pim/template/filter/product/identifier'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseFilter.extend({
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
