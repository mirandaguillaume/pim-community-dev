import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import FetcherRegistry from 'pim/fetcher-registry';
import template from 'pim/template/channel/tab/properties/general/category-tree';
import UserContext from 'pim/user-context';
import * as i18n from 'pim/i18n';
import 'jquery.select2';

export default BaseForm.extend({
  className: 'AknFieldContainer',
  template: _.template(template),
  catalogLocale: UserContext.get('catalogLocale'),

  /**
   * Initializes configuration.
   *
   * @param {Object} config
   */
  initialize: function (config) {
    this.config = config.config;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    FetcherRegistry.getFetcher('channel')
      .fetchCategoryTree()
      .then(
        function (categories) {
          if (0 === this.getFormData().category_tree.length) {
            var data = this.getFormData();
            data.category_tree = _.first(categories).code;
            this.setData(data, {silent: true});
          }

          this.$el.html(
            this.template({
              categoryTree: this.getFormData().category_tree,
              categories: categories,
              catalogLocale: this.catalogLocale,
              label: __('pim_enrich.entity.channel.property.category_tree'),
              requiredLabel: __('pim_common.required_label'),
              defaulValueLabel: __('pim_enrich.entity.channel.property.label_category_tree'),
              errors: this.getParent().getValidationErrorsForField('category'),
              i18n: i18n,
            })
          );

          this.$('.select2').select2().on('change', this.updateState.bind(this));
          this.renderExtensions();
        }.bind(this)
      );

    return this;
  },

  /**
   * Sets new category tree on change.
   *
   * @param {Object} event
   */
  updateState: function (event) {
    this.setCategory(event.currentTarget.value);
  },

  /**
   * Sets specified category tree into root model.
   *
   * @param {Array} code
   */
  setCategory: function (code) {
    var data = this.getFormData();

    data.category_tree = code;
    this.setData(data);
  },
});
