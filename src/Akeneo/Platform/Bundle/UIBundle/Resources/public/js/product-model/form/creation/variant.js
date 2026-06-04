import $ from 'jquery';
import 'underscore';
import 'oro/translator';
import FetcherRegistry from 'pim/fetcher-registry';
import Routing from 'pim/router';
import UserContext from 'pim/user-context';
import SimpleSelectAsync from 'pim/form/common/fields/simple-select-async';

export default SimpleSelectAsync.extend({
  previousFamily: null,

  /**
   * {@inheritdoc}
   */
  initialize() {
    SimpleSelectAsync.prototype.initialize.apply(this, arguments);

    this.previousFamily = null;
    this.readOnly = true;
  },

  /**
   * {@inheritdoc}
   */
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.onPostUpdate.bind(this));

    return SimpleSelectAsync.prototype.configure.apply(this, arguments);
  },

  /**
   * Updates the choice URL when the model changed
   */
  onPostUpdate() {
    if (this.getFormData().family !== this.previousFamily) {
      this.previousFamily = this.getFormData().family;

      if (this.getFormData().family) {
        this.getFamilyIdFromCode(this.getFormData().family).then(familyId => {
          this.setChoiceUrl(
            Routing.generate(this.config.loadUrl, {
              family_id: familyId,
            })
          );
          this.readOnly = false;
          this.setData({[this.fieldName]: null});

          this.render();
        });
      } else {
        this.readOnly = true;
        this.setData({[this.fieldName]: null});

        this.render();
      }
    }
  },

  /**
   * Get the id for a given family code
   *
   * @param {String} code
   *
   * @return {Promise}
   */
  getFamilyIdFromCode(code) {
    return FetcherRegistry.getFetcher('family')
      .fetch(code)
      .then(family => family.meta.id);
  },

  /**
   * Get the label of a family variant from its code
   *
   * @param {String} code
   *
   * @return {Promise}
   */
  getFamilyVariantLabelFromCode(code) {
    return FetcherRegistry.getFetcher('family-variant')
      .fetch(code)
      .then(familyVariant => familyVariant.labels[UserContext.get('catalogLocale')] || `[${familyVariant.code}]`);
  },

  /**
   * {@inheritdoc}
   */
  select2InitSelection(element, callback) {
    const id = $(element).val();
    if ('' !== id) {
      this.getFamilyVariantLabelFromCode(id).then(function (label) {
        callback({
          id: id,
          text: label,
        });
      });
    }
  },

  /**
   * {@inheritdoc}
   *
   * We override this method to automatically select the first element when there is only 1 choice
   */
  postRender() {
    SimpleSelectAsync.prototype.postRender.apply(this, arguments);

    if (!this.getFormData()[this.fieldName] && this.choiceUrl) {
      $.getJSON(this.choiceUrl, this.select2Data.bind(this)).then(response => {
        const results = this.select2Results(response).results;

        if (results.length === 1) {
          this.setData({[this.fieldName]: results[0].id});
          this.$('.select2').select2('val', results[0].id);
        } else {
          this.setData({[this.fieldName]: null});
          this.$('.select2').select2('val', '');
        }
      });
    }
  },
});
