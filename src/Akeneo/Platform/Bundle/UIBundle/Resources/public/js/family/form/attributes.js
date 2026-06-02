'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
require('pim/fetcher-registry');
var template = __pimInterop(require('pim/template/family/tab/attributes'));

module.exports = BaseForm.extend({
  className: 'attributes',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.code,
      label: __(this.config.label),
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(this.template());

    this.renderExtensions();
  },
});
