import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';
import React from 'react';
import ReactDOM from 'react-dom';
import BaseForm from 'pim/form';
import templateModalContent from 'pim/template/form/creation/modal';
import DatagridState from 'pim/datagrid/state';
import DatagridViewSaver from 'pim/saver/datagrid-view';
import * as messenger from 'oro/messenger';
import ViewSelectorActionLink from './ViewSelectorActionLink';
import CreateViewFields from './CreateViewFields';

export default BaseForm.extend({
  templateModalContent: _.template(templateModalContent),
  tagName: 'span',
  className: 'create-button',
  events: {
    'click .create': 'promptCreateView',
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if ('view' !== this.getRoot().currentViewType) {
      this.unmountReact();
      this.$el.empty();

      return this;
    }

    this.renderReact(
      ViewSelectorActionLink,
      {action: 'create', label: __('pim_datagrid.view_selector.create_view')},
      this.el
    );

    return this;
  },

  /**
   * Prompt the view-creation modal. The legacy Backbone.BootstrapModal chrome is kept (so
   * `.modal`/`.modal-body`/`.ok` and the PIM-wide "fill in the popin" Behat step keep working);
   * only the fields are rendered with React (CreateViewFields), with their state lifted here.
   */
  promptCreateView: function () {
    this.getRoot().trigger('grid:view-selector:close-selector');

    this.viewToCreate = {label: '', isPrivate: true};

    const modal = new Backbone.BootstrapModal({
      subtitle: __('pim_datagrid.view_selector.view'),
      title: __('pim_common.create'),
      picture: 'illustrations/Views.svg',
      okText: __('pim_common.save'),
      okCloses: false,
      content: this.templateModalContent({fields: ''}),
    });

    modal.open();

    const $submitButton = modal.$el.find('.ok').addClass('AknButton--disabled');
    this.modalFieldsContainer = modal.$el.find('[data-drop-zone="fields"]').get(0);

    ReactDOM.render(
      React.createElement(CreateViewFields, {
        labels: {
          chooseLabel: __('pim_datagrid.view_selector.choose_label'),
          placeholder: __('pim_datagrid.view_selector.placeholder'),
          chooseType: __('pim_datagrid.view_selector.choose_type'),
        },
        onChange: state => {
          this.viewToCreate = state;
          $submitButton.toggleClass('AknButton--disabled', 0 === state.label.length);
        },
        onSubmit: () => $submitButton.trigger('click'),
      }),
      this.modalFieldsContainer
    );

    modal.on('ok', this.saveView.bind(this, modal));
    modal.on(
      'cancel',
      function () {
        this.unmountModalFields();
        modal.remove();
      }.bind(this)
    );
  },

  /**
   * Unmount the React fields rendered inside the modal (the modal lives outside this view's el,
   * so it is not covered by BaseView.remove()/unmountReact()).
   */
  unmountModalFields: function () {
    if (this.modalFieldsContainer) {
      ReactDOM.unmountComponentAtNode(this.modalFieldsContainer);
      this.modalFieldsContainer = null;
    }
  },

  /**
   * {@inheritdoc}
   *
   * The modal-fields React tree lives outside this view's `el`, so BaseView.remove()
   * (which only unmounts `this.el`'s reactRef) would orphan it if the view is torn down
   * while the create modal is open. Unmount it explicitly first.
   */
  remove: function () {
    this.unmountModalFields();

    return BaseForm.prototype.remove.apply(this, arguments);
  },

  /**
   * Save the new Datagrid view in database and trigger an event to the parent to select it.
   *
   * @param {object} modal
   */
  saveView: function (modal) {
    if (modal.$el.find('.ok').hasClass('AknButton--disabled')) {
      return;
    }

    var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns']);
    var newView = {
      filters: gridState.filters,
      columns: gridState.columns,
      label: this.viewToCreate.label,
      type: this.viewToCreate.isPrivate ? 'private' : 'public',
    };

    DatagridViewSaver.save(newView, this.getRoot().gridAlias)
      .done(
        function (response) {
          this.getRoot().trigger('grid:view-selector:view-created', response.id);
          this.unmountModalFields();
          modal.close();
          modal.remove();
        }.bind(this)
      )
      .fail(function (response) {
        _.each(response.responseJSON, function (error) {
          messenger.notify('error', error);
        });
      });
  },
});
