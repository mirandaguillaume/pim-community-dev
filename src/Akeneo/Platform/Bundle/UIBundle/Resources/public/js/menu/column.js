'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var Column = __pimInterop(require('pim/form/common/column'));
var router = __pimInterop(require('pim/router'));
var mediator = __pimInterop(require('oro/mediator'));

module.exports = Column.extend({
  active: false,
  isVisible: true,

  /**
   * {@inheritdoc}
   */
  initialize: function () {
    mediator.on('pim_menu:highlight:tab', this.highlight, this);
    mediator.on('pim_menu:hide', this.hide, this);

    Column.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (this.active && this.isVisible) {
      return Column.prototype.render.apply(this, arguments);
    } else {
      return this.$el.empty();
    }
  },

  /**
   * Highlight or un-highlight tab
   *
   * @param {Event} event
   * @param {string} event.extension The extension code to highlight
   * @param {string} event.columnExtension The extension code of the column to activate
   */
  highlight: function (event) {
    if (event.columnExtension) {
      this.active = event.columnExtension === this.code;
    } else {
      this.active = event.extension === this.getTab();
    }
    this.isVisible = true;

    this.render();
  },

  hide: function (menuIdentifier) {
    if (this.code === menuIdentifier) {
      this.isVisible = false;
    }

    this.render();
  },

  /**
   * Returns the code of the attached tab
   *
   * @returns {string}
   */
  getTab: function () {
    return this.config.tab;
  },

  /**
   * The DOM element contains a `data-tab` attribute for compatibility with tab Bootstram tabs.
   *
   * {@inheritdoc}
   */
  redirect: function (event) {
    const item = this.findNavigationItemByRoute(event.currentTarget.dataset.tab);
    if (undefined === item) {
      throw new Error(`Navigation Item for route "${event.currentTarget.dataset.tab}" not found.`);
    }
    router.redirectToRoute(item.route, item.routeParams);
  },

  /**
   * Registers a new item to display on navigation template
   *
   * @param {Event}    navigationItem
   * @param {string}   navigationItem.label
   * @param {function} navigationItem.isVisible
   * @param {string}   navigationItem.route
   * @param {number}   navigationItem.position
   */
  registerNavigationItem: function (navigationItem) {
    Column.prototype.registerNavigationItem.apply(this, arguments);

    this.getRoot().trigger('pim_menu:register_item', {
      target: this.getTab(),
      route: navigationItem.route,
      position: navigationItem.position,
      routeParams: navigationItem.routeParams,
    });
  },
});
