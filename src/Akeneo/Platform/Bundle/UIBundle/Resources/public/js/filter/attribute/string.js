'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/attribute/attribute'));
require('pim/fetcher-registry');
require('pim/user-context');
require('pim/i18n');
var template = __pimInterop(require('pim/template/filter/attribute/string'));
require('jquery.select2');

module.exports = BaseFilter.extend({
  shortname: 'string',
  template: _.template(template),
  events: {
    'change [name="filter-operator"], [name="filter-value"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:pre_update',
      function (data) {
        _.defaults(data, {field: this.getCode(), value: '', operator: _.first(this.config.operators)});
      }.bind(this)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inherit}
   */
  isEmpty: function () {
    return (
      !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
      (undefined === this.getValue() || '' === this.getValue())
    );
  },

  /**
   * {@inherit}
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
    this.$('.operator').select2({minimumResultsForSearch: -1});
  },

  /**
   * {@inherit}
   */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val();
    var operator = this.$('[name="filter-operator"]').val();

    this.setData({
      field: this.getField(),
      operator: operator,
      value: value,
    });

    this.render();
  },
});
