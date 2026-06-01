function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var __ = __pimInterop(require('oro/translator'));
var ChoiceFilter = __pimInterop(require('oro/datafilter/choice-filter'));
('use strict');

module.exports = ChoiceFilter.extend({
  initialize: function () {
    this.choices = [
      {label: __('pim_datagrid.filters.common.contains'), value: '1'},
      {label: __('pim_datagrid.filters.common.does_not_contain'), value: '2'},
      {label: __('pim_datagrid.filters.common.equal'), value: '3'},
      {label: __('pim_datagrid.filters.common.start_with'), value: '4'},
      {label: __('pim_datagrid.filters.common.in_list'), value: 'in'},
      {label: __('pim_datagrid.filters.common.empty'), value: 'empty'},
      {label: __('pim_datagrid.filters.common.not_empty'), value: 'not empty'},
    ];
    this.emptyValue = {type: 'in', value: ''};

    ChoiceFilter.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  _getOperatorChoices() {
    return {
      1: __('pim_datagrid.filters.common.contains'),
      2: __('pim_datagrid.filters.common.does_not_contain'),
      3: __('pim_datagrid.filters.common.equal'),
      4: __('pim_datagrid.filters.common.start_with'),
      in: __('pim_datagrid.filters.common.in_list'),
      empty: __('pim_datagrid.filters.common.empty'),
      'not empty': __('pim_datagrid.filters.common.not_empty'),
    };
  },
});
