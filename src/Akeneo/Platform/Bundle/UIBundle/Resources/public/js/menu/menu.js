'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/menu/menu'));

module.exports = BaseForm.extend({
  className: 'AknHeader',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty().append(this.template());

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  renderExtension: function (extension) {
    if (
      !_.isEmpty(extension.options.config) &&
      (!extension.options.config.to || extension.options.config.isLandingSectionPage) &&
      _.isFunction(extension.hasChildren) &&
      !extension.hasChildren()
    ) {
      return;
    }

    BaseForm.prototype.renderExtension.apply(this, arguments);
  },
});
