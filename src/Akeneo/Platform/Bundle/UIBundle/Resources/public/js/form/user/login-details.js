import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/user/login-details';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    var user = this.getFormData();
    var createdDate = new Date(user.meta.created * 1000);
    var updatedDate = new Date(user.meta.updated * 1000);
    var lastLoginDate = new Date(user.last_login * 1000);
    this.$el.html(
      this.template({
        __,
        created: createdDate.toLocaleString(),
        updated: updatedDate.toLocaleString(),
        lastLoggedIn: lastLoginDate.toLocaleString(),
        loginCount: user.login_count,
      })
    );

    return BaseForm.prototype.render.apply(this, arguments);
  },
});
