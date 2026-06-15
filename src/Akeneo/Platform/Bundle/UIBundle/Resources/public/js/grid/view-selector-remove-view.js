import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import Dialog from 'pim/dialog';
import UserContext from 'pim/user-context';
import DatagridViewRemover from 'pim/remover/datagrid-view';
import * as messenger from 'oro/messenger';
import ViewSelectorActionLink from './ViewSelectorActionLink';

export default BaseForm.extend({
  tagName: 'span',
  className: 'remove-button',
  events: {
    'click .remove': 'promptDeletion',
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (
      'view' !== this.getRoot().currentViewType ||
      this.getRoot().currentView.id === 0 ||
      UserContext.get('meta').id !== this.getRoot().currentView.owner_id
    ) {
      this.unmountReact();
      this.$el.empty();

      return this;
    }

    this.renderReact(
      ViewSelectorActionLink,
      {action: 'remove', label: __('pim_datagrid.view_selector.remove')},
      this.el
    );

    return this;
  },

  /**
   * Prompt the datagrid view deletion modal.
   */
  promptDeletion: function (event) {
    event.stopPropagation();

    Dialog.confirm(
      __('pim_datagrid.view_selector.confirmation.remove'),
      __('pim_common.delete'),
      function () {
        this.removeView(this.getRoot().currentView);
      }.bind(this)
    );
  },

  /**
   * Remove the current Datagrid View and triggers an event to the parent.
   *
   * @param {Object} view
   */
  removeView: function (view) {
    DatagridViewRemover.remove(view)
      .done(
        function () {
          this.getRoot().trigger('grid:view-selector:view-removed');
        }.bind(this)
      )
      .fail(function (response) {
        var errors = response.responseJSON;

        if (_.isArray(errors)) {
          _.each(errors, function (error) {
            messenger.notify('error', error);
          });
        } else {
          messenger.notify('error', errors);
        }
      });
  },
});
