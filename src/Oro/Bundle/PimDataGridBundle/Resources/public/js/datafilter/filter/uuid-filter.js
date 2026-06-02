function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var __ = __pimInterop(require('oro/translator'));
var ChoiceFilter = __pimInterop(require('oro/datafilter/choice-filter'));
('use strict');

module.exports = ChoiceFilter.extend({
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
