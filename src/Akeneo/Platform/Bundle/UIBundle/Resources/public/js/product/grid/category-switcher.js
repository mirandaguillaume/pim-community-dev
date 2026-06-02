function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/grid/category-switcher'));
var Resizable = __pimInterop(require('pim/menu/resizable'));

module.exports = BaseForm.extend({
  template: _.template(template),
  className: 'AknDropdown AknColumn-block category-switcher',
  events: {
    click: 'toggleThirdColumn',
  },
  isOpen: false,
  categoryLabel: null,
  treeLabel: null,
  outsideEventListener: null,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:category_updated', this.updateValue);
    this.listenTo(this.getRoot(), 'grid:third_column:toggle', this.updateHighlight);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render() {
    if (null === this.treeLabel || '' === this.treeLabel.trim()) {
      return;
    }

    this.$el.html(
      this.template({
        label: __('pim_enrich.entity.category.uppercase_label'),
        isOpen: this.isOpen,
        categoryLabel: this.categoryLabel,
        treeLabel: this.treeLabel,
      })
    );

    this.renderExtensions();
  },

  /**
   * {@inheritdoc}
   */
  shutdown() {
    Resizable.destroy();

    return BaseForm.prototype.shutdown.apply(this, arguments);
  },

  /**
   * Toggle the thrid column
   */
  toggleThirdColumn() {
    Resizable.set({
      maxWidth: 500,
      minWidth: 300,
      container: '.AknDefault-thirdColumn',
      storageKey: 'category-switcher',
    });

    this.getRoot().trigger('grid:third_column:toggle');

    if (!this.isOpen) {
      this.outsideEventListener = this.outsideClickListener.bind(this);
      document.addEventListener('mousedown', this.outsideEventListener);
    }

    this.isOpen = !this.isOpen;
    this.render();
  },

  /**
   * Closes the criteria if the user clicks on the rest of the document.
   *
   * @param {Event} event
   */
  outsideClickListener(event) {
    const isOpen = $('.AknDefault-thirdColumnContainer--open').length > 0;
    const clickedFilter = $(event.target).closest('.AknFilterBox-addFilterButton').length > 0;

    if (isOpen && clickedFilter) {
      Resizable.destroy();
      this.toggleThirdColumn();
      document.removeEventListener('mousedown', this.outsideEventListener);
    }
  },

  /**
   * Updates the current category and tree
   *
   * @param {Object} value
   * @param {String} value.categoryLabel
   * @param {String} value.treeLabel
   */
  updateValue(value) {
    this.categoryLabel = value.categoryLabel;
    this.treeLabel = value.treeLabel;

    this.render();
  },

  /**
   * Updates the highlighted categories
   */
  updateHighlight() {
    this.isHighlited = !this.isHighlited;
    this.render();
  },
});
