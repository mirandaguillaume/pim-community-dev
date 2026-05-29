'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
require('backbone');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/grid/view-selector/line'));

module.exports = BaseForm.extend({
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
