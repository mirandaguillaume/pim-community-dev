/**
 * Extension to add a "remove" button on an optional filter.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
require('oro/translator');
var BaseForm = __pimInterop(require('pim/form'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));

module.exports = BaseForm.extend({
  /**
   * {@inherit}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:filter:set-default', this.addFilter.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * Adds filters to the collection.
   *
   * @param {Object} event
   */
  addFilter: function (event) {
    event.push(
      fetcherRegistry
        .getFetcher('attribute')
        .fetchByTypes(this.config.types)
        .then(function (attributes) {
          return _.pluck(attributes, 'code');
        })
    );
  },
});
