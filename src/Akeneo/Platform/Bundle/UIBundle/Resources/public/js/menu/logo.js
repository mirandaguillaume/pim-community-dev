'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var router = __pimInterop(require('pim/router'));
var template = __pimInterop(require('pim/template/menu/logo'));

module.exports = BaseForm.extend({
  className: 'AknHeader-menuItemContainer',
  template: _.template(template),
  events: {
    click: 'backHome',
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(this.template());

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * Redirect the user to app's home
   */
  backHome: function () {
    if (_.isUndefined(this.options.config.to)) {
      router.redirectToRoute('oro_default');
    } else {
      router.redirectToRoute(this.options.config.to);
    }
  },
});
