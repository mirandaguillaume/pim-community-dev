import BaseForm from 'pim/form';
import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import FetcherRegistry from 'pim/fetcher-registry';
import template from 'pim/template/product-model-edit-form/add-child-form-header';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render() {
    const parentCode = this.getFormData().parent;

    FetcherRegistry.getFetcher('product-model-by-code')
      .fetch(parentCode)
      .then(parent => {
        FetcherRegistry.getFetcher('family-variant')
          .fetch(parent.family_variant)
          .then(familyVariant => {
            this.getAxesAttributes(familyVariant, parent.meta.level + 1).then(axesAttributes => {
              const catalogLocale = UserContext.get('catalogLocale');
              const axesLabels = axesAttributes.map(attribute => {
                return i18n.getLabel(attribute.labels, catalogLocale, attribute.code);
              });

              this.$el.html(
                this.template({
                  __: __,
                  axes: axesLabels.sort().join(', '),
                  axesCount: axesLabels.length,
                })
              );
            });
          });
      });
  },

  /**
   * Looks for the attributes set corresponding to the specified level of the family variant
   * and fetches its axes attributes.
   *
   * @param {Object} familyVariant
   * @param {Number} level
   *
   * @returns {Promise}
   */
  getAxesAttributes(familyVariant, level) {
    const variantAttributeSets = familyVariant.variant_attribute_sets;
    const variantAttributeSetForLevel = variantAttributeSets.find(
      variantAttributeSet => variantAttributeSet.level === level
    );

    return FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(variantAttributeSetForLevel.axes);
  },
});
