function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var $ = __pimInterop(require('jquery'));
require('pim/form-builder');
var BaseForm = __pimInterop(require('pim/form'));
var CategoryFilter = __pimInterop(require('oro/datafilter/product_category-filter'));

module.exports = BaseForm.extend({
  config: {
    alias: 'product-grid',
    categoryTreeName: 'pim_enrich_product_grid_category_tree',
  },
  id: 'tree',
  className: 'filter-item',
  attributes: {
    'data-name': 'category',
    'data-type': 'tree',
    'data-relatedentity': 'product',
  },

  /**
   * @inheritdoc
   */
  initialize(options) {
    this.config = Object.assign(this.config, options.config || {});

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * @inheritDoc
   */
  configure() {
    this.listenTo(this.getRoot(), 'datagrid:getParams', this.setupCategoryTree);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Render the category tree extensions when the datagrid is ready
   */
  setupCategoryTree(urlParams) {
    const categoryFilter = new CategoryFilter(
      urlParams,
      this.config.alias,
      this.config.categoryTreeName,
      this.$el,
      value => {
        this.valueUpdated(value);
      }
    );

    this.listenTo(categoryFilter, 'update', function (value) {
      this.valueUpdated(value);
    });

    this.listenTo(categoryFilter, 'update_label', function (treeLabel, categoryLabel) {
      this.getRoot().trigger('pim_enrich:form:category_updated', {
        categoryLabel: categoryLabel ? this.trimCount(categoryLabel) : '',
        treeLabel: this.trimCount(treeLabel),
      });
    });

    return categoryFilter;
  },

  /**
   * Triggers a new event when the value of the category is updated
   *
   * @param {Object} value
   * @param {integer} value.type
   * @param {integer} value.value.categoryId
   * @param {integer} value.value.treeId
   */
  valueUpdated(value) {
    this.getRoot().trigger('pim_enrich:form:category_updated', {
      categoryLabel: this.getCategoryLabel(value.value.categoryId),
      treeLabel: this.getTreeLabel(),
    });
  },

  /**
   * Get the category label from its id.
   * We search the matching DOM element in the JStree plugin directly, because it does not exist any fetcher
   * able to get the label from its id.
   *
   * @returns {String}
   */
  getCategoryLabel() {
    return this.trimCount($('#tree [aria-selected=true]').text().trim());
  },

  /**
   * Get the current tree label.
   * See this.getCategoryLabel
   *
   * @returns {String}
   */
  getTreeLabel() {
    const button = $('#tree [role=tree] [role=treeitem] button[title]:first');
    if (button.length) {
      return this.trimCount(button.text().trim());
    }

    return '';
  },

  /**
   * Deletes the count of the category and the tree to only keep the label
   * For example, "Audio (123)" will return "Audio".
   *
   * @param {String} str
   *
   * @returns {String}
   */
  trimCount(str) {
    return str.replace(/(.*) \(\d+\)/, (match, text) => text);
  },
});
