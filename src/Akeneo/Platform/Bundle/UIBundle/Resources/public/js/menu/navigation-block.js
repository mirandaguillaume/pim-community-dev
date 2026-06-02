'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/menu/navigation-block'));

module.exports = BaseForm.extend({
  className: 'AknColumn-block',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * Proxy for 'pim_menu:column:register_navigation_item' event
   *
   * {@inheritdoc}
   */
  configure: function () {
    this.onExtensions('pim_menu:column:register_navigation_item', function (event) {
      this.trigger('pim_menu:column:register_navigation_item', event);
    });

    BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    BaseForm.prototype.render.apply(this, arguments);

    if (this.$el.html().trim() !== '') {
      this.$el.prepend(
        this.template({
          title: __(this.config.title),
        })
      );
    }
  },
});
