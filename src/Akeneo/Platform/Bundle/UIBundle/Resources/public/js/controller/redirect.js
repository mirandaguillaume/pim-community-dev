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
    return $.get(path).then(this.redirect.bind(this)).promise();
  },

  /**
   * Redirect to the given route
   *
   * @param {Object} response
   */
  redirect: function (response) {
    if (!this.active) {
      return;
    }

    router.redirectToRoute(response.route, response.params ? response.params : {}, {trigger: true});
  },
});
