'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var BaseForm = __pimInterop(require('pim/form'));
require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/product-model-edit-form/add-child-form-header'));

module.exports = BaseForm.extend({
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
