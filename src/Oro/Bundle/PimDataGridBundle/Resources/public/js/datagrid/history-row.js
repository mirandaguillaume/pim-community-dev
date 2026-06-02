function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('backgrid');
var BaseRow = __pimInterop(require('oro/datagrid/row'));
var versionTemplate = __pimInterop(require('pim/template/datagrid/row/version'));
var changesTemplate = __pimInterop(require('pim/template/datagrid/row/changes'));
('use strict');

module.exports = BaseRow.extend({
  tagName: 'div',
  versionTemplate: _.template(versionTemplate),
  changesTemplate: _.template(changesTemplate),

  /**
       Renders a row of cells for this row's model.
      */
  render: function () {
    this.$el.empty();

    const mainLine = $(
      this.versionTemplate({
        version: this.model.get('version'),
        id: this.model.get('id'),
      })
    );
    const changesLine = $(
      this.changesTemplate({
        version: this.model.get('version'),
        id: this.model.get('id'),
      })
    );

    mainLine.on('click', function () {
      changesLine.toggle();
      mainLine.toggleClass('AknGrid-bodyRow--expanded');
      mainLine.find('.AknGrid-expand').toggleClass('AknGrid-expand--expanded');
    });

    for (let i = 0; i < this.cells.length; i++) {
      const cell = this.cells[i];
      const line = 'changes' === cell.column.get('name') ? changesLine : mainLine;
      line.append(cell.render().el);
      if (!cell.column.get('renderable') && 'changes' !== cell.column.get('name')) cell.$el.hide();
    }

    this.$el.append(mainLine);
    this.$el.append(changesLine);
    changesLine.hide();

    this.delegateEvents();

    return this;
  },
});
