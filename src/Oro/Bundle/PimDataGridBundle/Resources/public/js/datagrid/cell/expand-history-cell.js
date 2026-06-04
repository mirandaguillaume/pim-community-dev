import _ from 'underscore';
import Backgrid from 'backgrid';
import template from 'pim/template/datagrid/cell/expand-history-cell';

export default Backgrid.StringCell.extend({
  /** @property */
  className: 'AknGrid-bodyCell AknGrid-expandable',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().html(
      this.template({
        value: this.model.get(this.column.get('name')),
      })
    );

    return this;
  },
});
