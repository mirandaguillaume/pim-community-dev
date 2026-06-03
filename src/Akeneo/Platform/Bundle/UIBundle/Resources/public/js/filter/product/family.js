'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/filter'));
var Routing = __pimInterop(require('routing'));
var template = __pimInterop(require('pim/template/filter/product/family'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var userContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
require('jquery.select2');

module.exports = BaseFilter.extend({
  shortname: 'family',
  config: {},
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    this.selectOptions = {
      allowClear: true,
      multiple: true,
      ajax: {
        url: Routing.generate(this.config.url),
        quietMillis: 250,
        cache: true,
        data: function (term, page) {
          return {
            search: term,
            options: {
              limit: 20,
              page: page,
              locale: userContext.get('uiLocale'),
            },
          };
        },
        results: function (families) {
          var data = {
            more: 20 === _.keys(families).length,
            results: [],
          };
          _.each(families, function (value, key) {
            data.results.push({
              id: key,
              text: i18n.getLabel(value.labels, userContext.get('uiLocale'), value.code),
            });
          });

          return data;
        },
      },
      initSelection: function (element, callback) {
        var families = this.getValue();
        if (null !== families) {
          fetcherRegistry
            .getFetcher('family')
            .fetchByIdentifiers(families)
            .then(function (families) {
              callback(
                _.map(families, function (family) {
                  return {
                    id: family.code,
                    text: i18n.getLabel(family.labels, userContext.get('uiLocale'), family.code),
                  };
                })
              );
            });
        }
      }.bind(this),
    };

    return BaseFilter.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:pre_update',
      function (data) {
        _.defaults(data, {field: this.getCode(), operator: '='});
      }.bind(this)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function () {
    return this.template({
      isEditable: this.isEditable(),
      __: __,
      field: this.getField(),
      value: this.getValue(),
      shortname: this.shortname,
    });
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('[name="filter-value"]').select2(this.selectOptions);
  },

  /**
   * {@inheritdoc}
   */
  isEmpty: function () {
    return _.isEmpty(this.getValue());
  },

  /**
   * {@inheritdoc}
   */
  updateState: function () {
    var value = this.$('[name="filter-value"]').val();

    this.setData({
      field: this.getField(),
      operator: 'IN',
      value: '' === value ? [] : value.split(','),
    });
  },
});
