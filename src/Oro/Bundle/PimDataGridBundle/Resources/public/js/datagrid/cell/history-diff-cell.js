import __ from 'oro/translator';
import Backgrid from 'backgrid';
import template from 'pim/template/datagrid/cell/history-diff-cell';

export default Backgrid.StringCell.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.el.setAttribute('colspan', 4);
    this.$el.empty();
    this.$el.html(
      this.template({
        changes: this.model.get(this.column.get('name')),
        __,
      })
    );
    this.delegateEvents();

    return this;
  },
});
