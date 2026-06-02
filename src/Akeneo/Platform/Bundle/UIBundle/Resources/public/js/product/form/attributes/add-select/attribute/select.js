'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('oro/translator');
var BaseAddSelect = __pimInterop(require('pim/common/add-select'));
var LineView = __pimInterop(require('pim/product/add-select/attribute/line'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
require('pim/attribute-manager');
var ChoicesFormatter = __pimInterop(require('pim/formatter/choices/base'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseAddSelect.extend({
  className: 'AknButtonList-item add-attribute',
  lineView: LineView,
  defaultConfig: {
    select2: {
      placeholder: 'pim_common.add_attributes',
      title: 'pim_common.select2.search',
      buttonTitle: 'pim_common.add',
      countTitle: 'pim_enrich.entity.attribute.module.add_attribute.attributes_selected',
      emptyText: 'pim_enrich.entity.attribute.module.add_attribute.no_available_attributes',
      classes: 'pim-add-attributes-multiselect',
      minimumInputLength: 0,
      dropdownCssClass: 'add-attribute',
      closeOnSelect: false,
    },
    resultsPerPage: 10,
    mainFetcher: 'attribute',
    events: {
      disable: null,
      enable: null,
      add: 'add-attribute:add',
    },
  },

  /**
   * {@inheritdoc}
   */
  getItemsToExclude: function () {
    return $.Deferred().resolve(_.keys(this.getFormData().values));
  },

  /**
   * This method is overridden to fetch attribute groups and set it inside attribute items.
   *
   * {@inheritdoc}
   */
  fetchItems: function (searchParameters) {
    if (undefined !== this.config.attributeTypes && Array.isArray(this.config.attributeTypes)) {
      searchParameters.types = this.config.attributeTypes.join(',');
    }

    return BaseAddSelect.prototype.fetchItems.apply(this, [searchParameters]).then(
      function (attributes) {
        var groupCodes = _.unique(_.pluck(attributes, 'group'));

        return FetcherRegistry.getFetcher('attribute-group')
          .fetchByIdentifiers(groupCodes)
          .then(
            function (attributeGroups) {
              return this.populateGroupProperties(attributes, attributeGroups);
            }.bind(this)
          );
      }.bind(this)
    );
  },

  /**
   * Transforms each attribute
   *
   * { code: 'name', group: 'marketing', ...  }
   *
   * into
   *
   * { code: 'name', group: { code: 'marketing', sort_order: 2, ... }, ...  }
   *
   * @param {Array} attributes
   * @param {Array} attributeGroups
   */
  populateGroupProperties: function (attributes, attributeGroups) {
    return _.map(attributes, function (attribute) {
      return _.extend(attribute, {group: _.findWhere(attributeGroups, {code: attribute.group})});
    });
  },

  /**
   * {@inheritdoc}
   */
  prepareChoices: function (items) {
    return _.chain(items)
      .map(function (item) {
        var group = ChoicesFormatter.formatOne(item.group);
        var choice = ChoicesFormatter.formatOne(item);
        choice.group = group;

        return choice;
      })
      .value();
  },

  /**
   * Triggers configured event with items codes selected
   */
  addItems: function () {
    this.trigger(this.addEvent, {codes: this.selection});

    analytics.appcuesTrack('grid:mass-edit:attributes-added', {
      codes: this.selection,
    });
  },
});
