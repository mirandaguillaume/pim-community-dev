import $ from 'jquery';
import __ from 'oro/translator';
import SelectFilter from 'oro/datafilter/select-filter';
import template from 'pim/template/datagrid/filter-grouped-variant';

export default SelectFilter.extend({
  template: _.template(template),
  className: 'AknDropdown AknDropdown--left AknTitleContainer-variantSelector',
  events: {
    'click .AknDropdown-menuLink': '_onValueChange',
  },
  placeholder: __('pim_datagrid.filters.entity_type.grouped'),

  _onValueChange: function (event) {
    const value = this.$(event.currentTarget).find('.display-grouped-item').data('value');
    this.setValue({value});
  },

  _onValueUpdated: function () {
    SelectFilter.prototype._onValueUpdated.apply(this, arguments);
    this._updateHighlight();
  },

  render: function () {
    SelectFilter.prototype.render.apply(this, arguments);
    this._updateHighlight();

    return this;
  },

  moveFilter: function (collection, element) {
    if (element.$('.search-zone').length !== 0) {
      element.$('.search-zone').append(this.$el.get(0));
    } else if ($('.edit-form .search-zone').length !== 0) {
      $('.edit-form .search-zone').append(this.$el.get(0));
    }
    this._updateHighlight();
  },

  _updateHighlight: function () {
    this._highlightDropdown(this.getValue().value || '', '');
  },
});
