'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/download-pdf'));
var Routing = __pimInterop(require('routing'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseForm.extend({
  tagName: 'a',

  className: 'AknDropdown-menuLink btn-download',

  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  configure: function () {
    UserContext.off('change:catalogLocale change:catalogScope', this.render);
    this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.getFormData().meta) {
      return;
    }

    this.$el.html(this.template());
    this.$el.attr(
      'href',
      Routing.generate('pim_pdf_generator_download_product_pdf', {
        uuid: this.getFormData().meta.id,
        dataLocale: UserContext.get('catalogLocale'),
        dataScope: UserContext.get('catalogScope'),
      })
    );

    return this;
  },
});
