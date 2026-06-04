import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/product/meta/family-variant';
import UserContext from 'pim/user-context';
import * as i18n from 'pim/i18n';

export default BaseForm.extend({
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
