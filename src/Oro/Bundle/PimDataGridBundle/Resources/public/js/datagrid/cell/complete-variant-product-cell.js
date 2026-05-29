function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var StringCell = __pimInterop(require('oro/datagrid/string-cell'));
var __ = __pimInterop(require('oro/translator'));
('use strict');

module.exports = StringCell.extend({
  /**
   * Render the completeness.
   */
  render: function () {
    if ('product_model' !== this.model.get('document_type')) {
      this.$el.empty().html(__('pim_common.not_available'));

      return this;
    }

    const data = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    let completeness = '-';

    if (null !== data && '' !== data) {
      let ratio = data.complete / data.total;
      let cssClass = '';
      if (1 === ratio) {
        cssClass += 'success';
      } else if (0 === ratio || 0 === data.total) {
        cssClass += 'important';
      } else {
        cssClass += 'warning';
      }

      completeness =
        '<span class="AknBadge AknBadge--' + cssClass + '">' + data.complete + ' / ' + data.total + '</span>';
    }

    this.$el.empty().html(completeness);

    return this;
  },
});
