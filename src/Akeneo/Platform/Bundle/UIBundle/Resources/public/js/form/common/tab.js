/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
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
    this.registerTab();

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  registerTab: function () {
    this.trigger('tab:register', {
      code: this.code,
      label: __(this.config.label),
    });
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    this.renderExtensions();
  },
});
