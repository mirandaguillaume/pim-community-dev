'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var AddAttributeSelect = __pimInterop(require('pim/product/add-select/attribute'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var ChoicesFormatter = __pimInterop(require('pim/formatter/choices/base'));
var LineView = __pimInterop(require('pim/common/add-select/line'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = AddAttributeSelect.extend({
  lineView: LineView,

  /**
   * Render this extension
   *
   * @return {Object}
   */
  render: function () {
    if (!this.hasRightToAdd()) {
      return this;
    }

    return AddAttributeSelect.prototype.render.apply(this, arguments);
  },

  /**
   * Creates request according to recieved options
   *
   * @param {Object} options
   */
  onGetQuery: function (options) {
    return FetcherRegistry.getFetcher('attribute')
      .search({
        identifiers: this.getParent().getOtherAttributes().join(','),
        rights: 0,
        search: options.term,
        options: {
          locale: UserContext.get('catalogLocale'),
        },
      })
      .then(this.prepareChoices)
      .then(function (choices) {
        options.callback({
          results: choices,
          more: false,
        });
      });
  },

  /**
   * {@inheritdoc}
   */
  prepareChoices: function (items) {
    return _.chain(items)
      .map(function (item) {
        var choice = ChoicesFormatter.formatOne(item);

        return choice;
      })
      .value();
  },

  /**
   * Does the user has right to add an attribute
   *
   * @return {Boolean}
   */
  hasRightToAdd: function () {
    return this.getParent().hasRightToAdd();
  },
});
