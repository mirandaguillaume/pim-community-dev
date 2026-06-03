/**
 * Boolean attribute filter.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseFilter = __pimInterop(require('pim/filter/attribute/attribute'));
require('pim/fetcher-registry');
require('pim/user-context');
require('pim/i18n');
var template = __pimInterop(require('pim/template/filter/attribute/boolean'));
require('bootstrap.bootstrapswitch');

module.exports = BaseFilter.extend({
  shortname: 'boolean',
  template: _.template(template),
  events: {
    'change [name="filter-value"]': 'updateState',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:entity:pre_update',
      function (data) {
        _.defaults(data, {field: this.getCode(), operator: '=', value: true});
      }.bind(this)
    );

    return BaseFilter.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(
      _.extend({}, templateContext, {
        value: this.getValue(),
        field: this.getField(),
        labels: {
          on: __('pim_common.yes'),
          off: __('pim_common.no'),
        },
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.switch').bootstrapSwitch();
  },

  /**
   * {@inheritdoc}
   */
  updateState: function () {
    this.setData({
      field: this.getField(),
      operator: '=',
      value: this.$('[name="filter-value"]').is(':checked'),
    });
  },
});
