'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var template = __pimInterop(require('pim/template/export/common/edit/meta'));

module.exports = BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        jobInstance: this.getFormData(),
        __: __,
      })
    );

    return this;
  },
});
