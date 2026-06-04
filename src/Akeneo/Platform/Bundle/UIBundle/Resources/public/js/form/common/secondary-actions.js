import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/secondary-actions';

export default BaseForm.extend({
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
