import 'jquery';
import _ from 'underscore';
import 'backbone';
import BaseForm from 'pim/form';
import template from 'pim/template/grid/view-selector/line';

export default BaseForm.extend({
  template: _.template(template),
  datagridView: null,
  datagridViewType: null,
  currentViewId: null,

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        view: this.datagridView,
        isCurrent: this.currentViewId === this.datagridView.id,
      })
    );

    this.renderExtensions();

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
