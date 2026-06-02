function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
require('backbone');
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/datagrid/display-selector'));
var Routing = __pimInterop(require('pim/router'));

module.exports = BaseForm.extend({
  className: 'AknDropdown AknDropdown--left AknTitleContainer-displaySelector',
  gridName: null,
  template: _.template(template),
  events: {
    'click .display-selector-item': 'setDisplayType',
  },

  /**
   * @inheritDoc
   */
  initialize(options) {
    this.gridName = options.config.gridName;

    if (null === this.gridName) {
      new Error('You must specify gridName for the display-selector');
    }

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * @inheritDoc
   */
  configure() {
    this.listenTo(this.getRoot(), 'grid_load:start', this.collectDisplayOptions.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Receives the grid displayTypes config from the gridView and
   * renders them (with translated labels);
   *
   * @param  {Backbone.Collection} collection The datagrid collection
   * @param  {Backbone.View} gridView   The datagrid view
   */
  collectDisplayOptions(collection, gridView) {
    const displayTypes = gridView.options.displayTypes;

    if (undefined === displayTypes) {
      return;
    }

    for (let display in displayTypes) {
      const type = displayTypes[display];
      type.label = __(type.label);
    }

    this.renderDisplayTypes(displayTypes);
  },

  /**
   * Returns the display type stored for a grid name
   * @return {String} The name of the display type e.g. thumbnail
   */
  getStoredType() {
    return localStorage.getItem(`display-selector:${this.gridName}`);
  },

  /**
   * Gets the name of the display type from the event target and
   * puts it in localStorage using the gridName as the key.
   *
   * @param {jQuery.Event} event The dropdown item click event
   */
  setDisplayType(event) {
    const type = this.$(event.target).data('type');

    localStorage.setItem(`display-selector:${this.gridName}`, type);

    return Routing.reloadPage();
  },

  /**
   * Renders the dropdown list to show the display types
   *
   * @param  {Object} types A config object containing the display types
   * @return {Function}
   */
  renderDisplayTypes(types) {
    const firstType = Object.keys(types)[0];
    let selectedType = this.getStoredType();
    const displayLabel = __('pim_datagrid.display_selector.label');

    if (undefined === types[selectedType]) {
      selectedType = firstType;
    }

    this.$el.html(
      this.template({
        displayLabel,
        types,
        selectedType,
      })
    );

    return BaseForm.prototype.render.apply(this, arguments);
  },
});
