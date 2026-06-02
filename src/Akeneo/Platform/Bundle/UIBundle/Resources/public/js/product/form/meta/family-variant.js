'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/meta/family-variant'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseForm.extend({
  className: 'AknColumn-block',

  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(UserContext, 'change:catalogLocale', this.render);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    const entity = this.getFormData();
    const familyVariant = entity.meta.family_variant;

    if (null === familyVariant) {
      return this;
    }

    const label = i18n.getLabel(
      familyVariant.labels,
      UserContext.get('catalogLocale'),
      entity.meta.family_variant.code
    );

    this.$el.html(
      this.template({
        title: __('pim_enrich.entity.family_variant.short_label'),
        familyVariantLabel: label,
      })
    );

    BaseForm.prototype.render.apply(this, arguments);
  },
});
