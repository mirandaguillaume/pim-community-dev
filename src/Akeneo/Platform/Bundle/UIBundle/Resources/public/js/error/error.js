'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Backbone = __pimInterop(require('backbone'));
var template = __pimInterop(require('pim/template/error/error'));

module.exports = Backbone.View.extend({
  template: _.template(template),
  initialize: function (message, statusCode) {
    this.message = message;
    this.statusCode = statusCode;
  },
  render: function () {
    this.$el.html(
      this.template({
        message: this.message,
        statusCode: this.statusCode,
      })
    );
  },
});
