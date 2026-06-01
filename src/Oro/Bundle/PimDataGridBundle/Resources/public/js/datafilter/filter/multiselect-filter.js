function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
require('oro/translator');
var SelectFilter = __pimInterop(require('oro/datafilter/select-filter'));
('use strict');

module.exports = SelectFilter.extend({
  /**
   * Multiselect filter template
   *
   * @property
   */
  template: _.template(
    '<div class="AknFilterBox-filter filter-select filter-criteria-selector">' +
      '<% if (showLabel) { %>' +
      '<span class="AknFilterBox-filterLabel"><%= label %></span>' +
      '<% } %>' +
      '<select multiple>' +
      '<% _.each(options, function (option) { %>' +
      '<% if(_.isObject(option.value)) { %>' +
      '<optgroup label="<%= option.label %>">' +
      '<% _.each(option.value, function (value) { %>' +
      '<option value="<%= value.value %>"><%= _.__(value.label) %></option>' +
      '<% }); %>' +
      '</optgroup>' +
      '<% } else { %>' +
      '<option value="<%= option.value %>"><%= _.__(option.label) %></option>' +
      '<% } %>' +
      '<% }); %>' +
      '</select>' +
      '</div>' +
      '<% if (canDisable) { %><a href="<%= nullLink %>" class="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter"></a><% } %>'
  ),

  /**
   * Select widget options
   *
   * @property
   */
  widgetOptions: {
    multiple: true,
    classes: 'AknFilterBox-filterCriteria select-filter-widget multiselect-filter-widget',
  },

  _onSelectChange: function () {
    var data = this._readDOMValue();

    // At initialization, the value is `''` which mean 'All' but it should be `['']`
    var previousValue = '' === this.getValue().value ? [''] : this.getValue().value;

    // We try to guess if the user added 'All' to remove all previous selection
    var addAll = _.contains(_.difference(data.value, previousValue), '');

    data.value = _.contains(data.value, '') ? _.without(data.value, '') : data.value;
    data.value = _.isEmpty(data.value) ? [''] : data.value;
    data.value = addAll ? [''] : data.value;

    // set value
    this.setValue(this._formatRawValue(data));

    // update dropdown
    this._setDropdownWidth();
    this._updateCriteriaSelectorPosition();
  },
});
