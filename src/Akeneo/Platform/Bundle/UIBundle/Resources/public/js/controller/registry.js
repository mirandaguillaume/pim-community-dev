'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var requireContext = __pimInterop(require('require-context'));
var config = __moduleConfig;
var controllers = config.controllers || {};
var defaultController = config.defaultController;

module.exports = {
  /**
   * Get the controller for the given name
   *
   * @param {String} name
   *
   * @return {Promise}
   */
  get: function (name) {
    var deferred = $.Deferred();
    var controller = controllers[name] || defaultController;
    var Controller = requireContext(controller.module);
    controller.class = Controller;
    deferred.resolve(controller);

    return deferred.promise();
  },
};
