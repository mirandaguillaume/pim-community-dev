'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/secondary-actions'));

module.exports = BaseForm.extend({
  className: 'AknSecondaryActions AknDropdown AknButtonList-item secondary-actions',

  template: _.template(template),

  /**
   * When there is no extensions attached to this module, nothing is rendered.
   * Each extension represents a secondary action available for the user.
   *
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    if (!_.isEmpty(this.extensions)) {
      this.$el.html(
        this.template({
          titleLabel: __('pim_datagrid.actions.other'),
        })
      );

      this.renderExtensions();
    }
  },
});
