import _ from 'underscore';
import BaseForm from 'pim/form';
import router from 'pim/router';
import template from 'pim/template/menu/logo';

export default BaseForm.extend({
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
