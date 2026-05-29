'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/form/meta'));

module.exports = BaseForm.extend({
  template: _.template(template),

  config: {},

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    if (!_.isEmpty(this.extensions)) {
      this.$el.html(
        this.template({
          label: __(this.config.label),
        })
      );
    }

    return BaseForm.prototype.render.apply(this, arguments);
  },
});
