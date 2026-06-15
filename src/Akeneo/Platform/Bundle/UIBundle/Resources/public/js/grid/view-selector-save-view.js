import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import DatagridState from 'pim/datagrid/state';
import 'pim/dialog';
import 'routing';
import UserContext from 'pim/user-context';
import DatagridViewSaver from 'pim/saver/datagrid-view';
import * as messenger from 'oro/messenger';
import analytics from 'pim/analytics';
import ViewSelectorActionLink from './ViewSelectorActionLink';

export default BaseForm.extend({
  tagName: 'span',
  className: 'save-button',
  events: {
    'click .save': 'saveView',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'grid:view-selector:state-changed', this.onDatagridStateChange);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (
      'view' !== this.getRoot().currentViewType ||
      UserContext.get('meta').id !== this.getRoot().currentView.owner_id
    ) {
      this.unmountReact();
      this.$el.empty();

      return;
    }

    this.renderReact(
      ViewSelectorActionLink,
      {action: 'save', label: __('pim_datagrid.view_selector.save_changes'), hidden: !this.dirty},
      this.el
    );
  },

  /**
   * Method called on datagrid state change (when columns or filters are modified)
   *
   * @param {Object} datagridState
   */
  onDatagridStateChange: function (datagridState) {
    var initialView = this.getRoot().initialView;
    var initialViewExists = null !== initialView && 0 !== initialView.id;

    if (initialViewExists) {
      var filtersModified = initialView.filters !== datagridState.filters;
      var columnsModified = !_.isEqual(initialView.columns, datagridState.columns.split(','));

      this.dirty = filtersModified || columnsModified;
      this.render();
    }
  },

  /**
   * Save the current Datagrid view in database and triggers an event to the parent
   * to select it.
   */
  saveView: function () {
    var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns']);

    var currentView = $.extend(true, {}, this.getRoot().currentView);
    currentView.filters = gridState.filters;
    currentView.columns = gridState.columns;

    DatagridViewSaver.save(currentView, this.getRoot().gridAlias)
      .done(
        function (response) {
          this.getRoot().trigger('grid:view-selector:view-saved', response.id);

          analytics.appcuesTrack('product-grid:view:saved', {
            name: currentView.label,
          });
        }.bind(this)
      )
      .fail(function (response) {
        _.each(response.responseJSON, function (error) {
          messenger.notify('error', error);
        });
      });
  },
});
