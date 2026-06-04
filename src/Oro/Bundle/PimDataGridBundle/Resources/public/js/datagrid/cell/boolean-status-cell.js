import StringCell from 'oro/datagrid/string-cell';
import __ from 'oro/translator';

export default StringCell.extend({
  render: function () {
    const columnValue = this.model.get(this.column.get('name'));
    const value = this.formatter.fromRaw(columnValue);
    const label = true === value || 'true' === value || '1' === value ? '<strong>' + __('Yes') + '</strong>' : __('No');

    this.$el.empty().html(label);

    return this;
  },
});
