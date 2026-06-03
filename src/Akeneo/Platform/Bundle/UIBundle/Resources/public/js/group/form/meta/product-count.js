'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var formTemplate = __pimInterop(require('pim/template/group/meta/product-count'));

module.exports = BaseForm.extend({
  tagName: 'span',
  template: _.template(formTemplate),

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
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    var group = this.getFormData();
    var html = '';

    if (_.has(group, 'products')) {
      html = this.template({
        label: __(this.config.productCountLabel),
        productCount: group.products.length,
      });
    }

    this.$el.html(html);

    return this;
  },
});
