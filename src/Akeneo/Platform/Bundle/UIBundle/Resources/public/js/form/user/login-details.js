'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/user/login-details'));

module.exports = BaseForm.extend({
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
