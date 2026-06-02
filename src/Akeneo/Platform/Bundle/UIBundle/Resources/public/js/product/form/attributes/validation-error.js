'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var Backbone = __pimInterop(require('backbone'));
var template = __pimInterop(require('pim/template/product/tab/attribute/validation-error'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = Backbone.View.extend({
  template: _.template(template),
  className: 'AknFieldContainer-validationErrors validation-errors',
  events: {
    'click .change-context': 'changeContext',
  },
  initialize: function (errors, parent) {
    this.errors = errors;
    this.parent = parent;
  },
  render: function () {
    this.$el.html(this.template({errors: this.errors, i18n: i18n}));
    this.delegateEvents();

    return this;
  },
  changeContext: function (event) {
    this.parent.changeContext(event.currentTarget.dataset.locale, event.currentTarget.dataset.scope);
  },
});
