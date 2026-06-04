import $ from 'jquery';
import _ from 'underscore';
import 'oro/translator';
import MassEditField from 'pim/mass-edit-form/product/mass-edit-field';
import 'pim/router';
import UserContext from 'pim/user-context';
import FetcherRegistry from 'pim/fetcher-registry';
import MediaUrlGenerator from 'pim/media-url-generator';
import templateProductModel from 'pim/template/product/form/variant-navigation/product-model-item';

export default MassEditField.extend({
  previousFamilyVariant: null,
  templateProductModel: _.template(templateProductModel),

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.onPostUpdate.bind(this));

    return MassEditField.prototype.configure.apply(this, arguments);
  },

  /**
   * When the model data is updated with a new family variant, drops the previous value and re-render the
   * field.
   */
  onPostUpdate() {
    if (this.getFormData().family_variant !== this.previousFamilyVariant) {
      this.previousFamilyVariant = this.getFormData().family_variant;
      this.setData({[this.fieldName]: null});

      this.render();
    }
  },

  /**
   * {@inheritdoc}
   *
   * This method overrides the previous one to be able to format the result and add an image and set a
   * custom template.
   */
  getSelect2Options() {
    let options = MassEditField.prototype.getSelect2Options.apply(this, arguments);

    options.dropdownCssClass = 'variant-navigation';
    options.formatResult = (item, $container) => {
      const filePath = null !== item.image ? item.image.filePath : null;
      const entity = {
        label: item.text,
        image: MediaUrlGenerator.getMediaShowUrl(filePath, 'thumbnail_small'),
      };

      $container.append(this.templateProductModel({entity: entity, getClass: this.getCompletenessBadgeClass}));
    };

    return options;
  },

  /**
   * {@inheritdoc}
   */
  select2Data() {
    let result = MassEditField.prototype.select2Data.apply(this, arguments);
    result.options.family_variant = this.getFormData().family_variant;

    return result;
  },

  /**
   * {@inheritdoc}
   */
  convertBackendItem(item) {
    return {
      id: item.code,
      text: `${item.code} - ${item.meta.label[UserContext.get('catalogLocale')]}`,
      image: item.meta.image || null,
    };
  },

  /**
   * {@inheritdoc}
   */
  isReadOnly() {
    return !this.getFormData().family_variant || MassEditField.prototype.isReadOnly.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  select2InitSelection(element, callback) {
    const id = $(element).val();
    if ('' !== id) {
      FetcherRegistry.getFetcher('product-model-by-code')
        .fetch(id)
        .then(productModel => {
          callback(this.convertBackendItem(productModel));
        });
    }
  },
});
