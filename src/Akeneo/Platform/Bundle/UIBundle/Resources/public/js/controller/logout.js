'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
var BaseController = __pimInterop(require('pim/controller/base'));
var router = __pimInterop(require('pim/router'));

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route, path) {
    return $.get(path)
      .then(() => {
        window.location = router.generate('pim_user_security_login');
      })
      .promise();
  },
});
