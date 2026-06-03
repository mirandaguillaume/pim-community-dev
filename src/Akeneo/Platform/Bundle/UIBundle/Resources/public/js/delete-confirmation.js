'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Backbone = __pimInterop(require('backbone'));

module.exports = Backbone.BootstrapModal.extend({
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    options = _.extend(
      {
        title: __('pim_common.confirm_deletion'),
        okText: __('pim_common.ok'),
        buttonClass: 'AknButton--important',
        illustrationClass: 'delete',
        cancelText: __('pim_common.cancel'),
      },
      options
    );

    arguments[0] = options;

    Backbone.BootstrapModal.prototype.initialize.apply(this, arguments);
  },
});
