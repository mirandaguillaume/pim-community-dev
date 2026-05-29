'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var formTemplate = __pimInterop(require('pim/template/form/meta/uuid'));

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

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    var product = this.getFormData();
    var html = this.template({
      label: this.label,
      uuid: _.result(product.meta, 'uuid', null),
    });

    this.$el.html(html);

    return this;
  },
});
