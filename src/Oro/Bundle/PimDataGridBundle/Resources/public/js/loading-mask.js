'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
require('underscore');
var Backbone = __pimInterop(require('backbone'));
require('oro/translator');

module.exports = Backbone.View.extend({
  /** @property {Boolean} */
  displayed: false,

  /** @property {String} */
  className: 'AknLoadingMask loading-mask',

  /**
   * Show loading mask
   *
   * @return {*}
   */
  show: function () {
    this.$el.show();
    this.displayed = true;

    return this;
  },

  /**
   * Hide loading mask
   *
   * @return {*}
   */
  hide: function () {
    this.$el.hide();
    this.displayed = false;

    return this;
  },

  /**
   * Render loading mask
   *
   * @return {*}
   */
  render: function () {
    this.hide();

    return this;
  },
});
