import StringCell from 'oro/datagrid/string-cell';

export default StringCell.extend({
  className: 'AknGrid-bodyCell AknGrid-bodyCell--noWrap AknGrid-bodyCell--highlight',
  render: function () {
    StringCell.prototype.render.apply(this, arguments);
    this.$el.prop('title', this.$el.text());

    return this;
  },
});
