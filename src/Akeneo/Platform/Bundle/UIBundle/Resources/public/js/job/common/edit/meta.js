import BaseForm from 'pim/form';
import _ from 'underscore';
import __ from 'oro/translator';
import template from 'pim/template/export/common/edit/meta';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        jobInstance: this.getFormData(),
        __: __,
      })
    );

    return this;
  },
});
