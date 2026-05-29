'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var formTemplate = __pimInterop(require('pim/template/form/meta/created'));

module.exports = BaseForm.extend({
  tagName: 'span',

  className: 'AknTitleContainer-metaItem',

  template: _.template(formTemplate),

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config;

    this.label = __(this.config.label);
    this.labelBy = __(this.config.labelBy);

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    var product = this.getFormData();
    var html = '';

    if (product.meta.created) {
      html = this.template({
        label: this.label,
        labelBy: this.labelBy,
        loggedAt: _.result(product.meta.created, 'logged_at', null),
        author: _.result(product.meta.created, 'author', null),
      });
    }

    this.$el.html(html);

    return this;
  },
});
