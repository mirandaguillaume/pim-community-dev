'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('oro/translator');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var Routing = __pimInterop(require('routing'));
var analytics = __pimInterop(require('pim/analytics'));
const ACTION_PRODUCT_GRID = 'product-edit';

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  initialize: function (options) {
    this.config = options.config;
  },

  /**
   * {@inheritdoc}
   */
  renderForm: function (route, path) {
    var query = path.replace(route.route.tokens[0][1], '');
    var parameters = _.chain(query.split('&'))
      .map(function (parameter) {
        return parameter.split('=');
      })
      .map(function (parameter) {
        return {
          key: parameter[0].replace('?', ''),
          value: parameter[1],
        };
      })
      .value();

    var actionName = _.find(parameters, function (parameter) {
      return 'actionName' === parameter.key;
    }).value.replace(new RegExp('_', 'g'), '-');

    const values = _.find(parameters, function (parameter) {
      return 'values' === parameter.key;
    }).value.split(',');
    const queryWithoutValues = query.replace(/&values=[^&]+/, '');

    analytics.appcuesTrack('grid:mass-edit:clicked', {
      name: actionName,
    });

    const url =
      actionName === ACTION_PRODUCT_GRID
        ? Routing.generate(this.config.route)
        : Routing.generate(this.config.route) + queryWithoutValues;
    const data = actionName === ACTION_PRODUCT_GRID ? query : {values};

    return $.post(url, data).then(response => {
      const filters = response.filters;
      const itemsCount = response.itemsCount;

      return FormBuilder.build('pim-mass-' + actionName).then(form => {
        form.setData({
          filters: filters,
          jobInstanceCode: null,
          actions: [],
          itemsCount: itemsCount,
        });

        form.setElement(this.$el).render();

        return form;
      });
    });
  },
});
