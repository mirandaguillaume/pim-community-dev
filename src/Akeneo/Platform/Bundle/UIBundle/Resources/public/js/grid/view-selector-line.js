import 'jquery';
import 'backbone';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import ViewSelectorLine from './ViewSelectorLine';

export default BaseForm.extend({
  datagridView: null,
  datagridViewType: null,
  currentViewId: null,

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.renderReact(
      ViewSelectorLine,
      {
        view: this.datagridView,
        isCurrent: this.currentViewId === this.datagridView.id,
        publicLabel: __('pim_datagrid.view_selector.public_label'),
      },
      this.el
    );

    return this;
  },

  /**
   * Set the view of this module.
   *
   * @param {Object}  view
   * @param {String}  viewType
   * @param {int}     currentViewId
   */
  setView: function (view, viewType, currentViewId) {
    this.datagridView = view;
    this.datagridViewType = viewType;
    this.currentViewId = currentViewId;
  },
});
