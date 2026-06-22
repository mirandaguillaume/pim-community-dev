import 'underscore';
import __ from 'oro/translator';
import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';

/**
 * React inner-render of the identifier datagrid filter (C1 Wave 4, Slice C4).
 *
 * Pure config subclass of the React `choice-filter-react` base (Slice C3): inherits ALL the React
 * rendering and only declares its 7 operators + default. Verbatim copy of `identifier-filter.js`,
 * only the base import differs.
 *
 * Added ALONGSIDE `identifier-filter.js`; only the `identifier` FilterTypeRegistry alias is re-pointed.
 */
export default ChoiceFilterReact.extend({
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

    ChoiceFilterReact.prototype.initialize.apply(this, arguments);
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
