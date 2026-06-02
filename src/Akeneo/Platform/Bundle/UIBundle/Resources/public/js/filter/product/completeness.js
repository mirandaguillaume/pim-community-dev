'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/filter'));
require('routing');
var template = __pimInterop(require('pim/template/filter/product/completeness'));
require('jquery.select2');

module.exports = BaseFilter.extend({
  shortname: 'completeness',
  template: _.template(template),
  events: {
    'change [name="filter-operator"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.parentForm.getRoot(), 'locales:update:after', this.updateState.bind(this));
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:pre_update',
      function (data) {
        _.defaults(data, {field: this.getCode(), operator: _.first(this.config.operators), value: 100});
      }.bind(this)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  },

  /**
   * Returns rendered input.
   *
   * @return {String}
   */
  renderInput: function () {
    return this.template({
      isEditable: this.isEditable(),
      __: __,
      operator: this.getOperator(),
      value: this.getValue(),
      operatorChoices: this.config.operators,
    });
  },

  /**
   * Initializes select2 after rendering.
   */
  postRender: function () {
    this.$('[name="filter-operator"]').select2({minimumResultsForSearch: -1});
  },

  /**
   * {@inheritdoc}
   */
  isEmpty: function () {
    return this.config.neverEmpty ? false : 'ALL' === this.getOperator();
  },

  /**
   * Updates operator and value on fields change.
   */
  updateState: function () {
    this.setData({
      field: this.getField(),
      operator: this.$('[name="filter-operator"]').val(),
      value: 100,
      context: {locales: this.getParentForm().getFilters().structure.locales},
    });
  },
});
