import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseFilter from 'pim/filter/attribute/attribute';
import 'pim/fetcher-registry';
import 'pim/user-context';
import 'pim/i18n';
import template from 'pim/template/filter/attribute/boolean';
import 'bootstrap.bootstrapswitch';

export default BaseFilter.extend({
  shortname: 'boolean',
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:pre_update',
      function (data) {
        _.defaults(data, {field: this.getCode(), operator: '=', value: true});
      }.bind(this)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(
      _.extend({}, templateContext, {
        value: this.getValue(),
        field: this.getField(),
        labels: {
          on: __('pim_common.yes'),
          off: __('pim_common.no'),
        },
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.switch').bootstrapSwitch();
  },

  /**
   * {@inheritdoc}
   */
  updateState: function () {
    this.setData({
      field: this.getField(),
      operator: '=',
      value: this.$('[name="filter-value"]').is(':checked'),
    });
  },
});
