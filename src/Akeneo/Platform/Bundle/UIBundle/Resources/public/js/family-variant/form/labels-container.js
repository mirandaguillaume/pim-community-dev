'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/family-variant/labels-container'));

module.exports = BaseForm.extend({
  render: function () {
    this.$el.html(
      _.template(template)({
        __: __,
        familyVariant: this.getFormData(),
      })
    );

    this.renderExtensions();
  },
});
