function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
var __ = __pimInterop(require('oro/translator'));
('use strict');

module.exports = StringCell.extend({
  /**
   * Render the boolean.
   */
  render: function () {
    var value = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    if (null === value || '' === value) {
      return this;
    }

    var status = true === value || 'true' === value || '1' === value ? 'success' : 'important';
    var label = true === value || 'true' === value || '1' === value ? __('pim_common.yes') : __('pim_common.no');

    this.$el.empty().html('<span class="AknBadge AknBadge--' + status + '">' + __(label) + '</span>');

    return this;
  },
});
