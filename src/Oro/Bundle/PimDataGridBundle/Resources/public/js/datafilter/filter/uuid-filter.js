import 'underscore';
import __ from 'oro/translator';
import ChoiceFilter from 'oro/datafilter/choice-filter';

export default ChoiceFilter.extend({
  initialize: function () {
    this.choices = [{label: __('pim_datagrid.filters.common.in_list'), value: 'in'}];
    this.emptyValue = {type: 'in', value: ''};

    ChoiceFilter.prototype.initialize.apply(this, arguments);
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
