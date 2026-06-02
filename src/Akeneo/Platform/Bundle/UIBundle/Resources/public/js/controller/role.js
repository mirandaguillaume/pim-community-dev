'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var FormController = __pimInterop(require('pim/controller/form'));
var securityContext = __pimInterop(require('pim/security-context'));
var configProvider = __pimInterop(require('pim/form-config-provider'));
var router = __pimInterop(require('pim/router'));

module.exports = FormController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route, path) {
    return securityContext.initialize().then(() => {
      if (!securityContext.isGranted('pim_user_role_edit')) {
        router.redirectToRoute('pim_dashboard_index');

        return;
      }

      return $.get(path).then(this.renderTemplate.bind(this)).promise();
    });
  },

  /**
   * {@inheritdoc}
   */
  afterSubmit: function () {
    securityContext.initialize();
    configProvider.clear();

    FormController.prototype.afterSubmit.apply(this, arguments);

    if (!this.$('#entity-updated span').is(':visible')) {
      location.reload();
    }
  },
});
