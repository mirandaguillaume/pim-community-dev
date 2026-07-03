import 'underscore';
import __ from 'oro/translator';
import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';

/**
 * React inner-render of the uuid datagrid filter (C1 Wave 4, Slice C4).
 *
 * Pure config subclass of the React `choice-filter-react` base (Slice C3): inherits ALL the React
 * rendering and only declares its single `in` operator + default. The single-operator AknDropdown
 * still renders (emptyChoice defaults true). Verbatim copy of `uuid-filter.js`, only the base import
 * differs.
 *
 * Added ALONGSIDE `uuid-filter.js`; only the `uuid` FilterTypeRegistry alias is re-pointed.
 */
export default ChoiceFilterReact.extend({
  initialize: function () {
    this.choices = [{label: __('pim_datagrid.filters.common.in_list'), value: 'in'}];
    this.emptyValue = {type: 'in', value: ''};

    ChoiceFilterReact.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  _getOperatorChoices() {
    return {
      in: __('pim_datagrid.filters.common.in_list'),
    };
  },
});
