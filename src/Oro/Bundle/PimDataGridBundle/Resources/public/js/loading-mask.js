import 'jquery';
import 'underscore';
import Backbone from 'backbone';
import 'oro/translator';

export default Backbone.View.extend({
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
