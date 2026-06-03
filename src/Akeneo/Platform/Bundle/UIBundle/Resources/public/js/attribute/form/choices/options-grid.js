/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var AttributeOptionGrid = __pimInterop(require('pim/attributeoptionview'));
var template = __pimInterop(require('pim/template/attribute/tab/choices/options-grid'));

module.exports = BaseForm.extend({
  template: _.template(template),
  locales: [],

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseForm.prototype.configure.apply(this, arguments),
      fetcherRegistry
        .getFetcher('locale')
        .fetchActivated()
        .then(
          function (locales) {
            this.locales = locales;
          }.bind(this)
        )
    );
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        attributeId: this.getFormData().meta.id,
        sortable: !this.getFormData().auto_option_sorting,
        localeCodes: _.pluck(this.locales, 'code'),
      })
    );

    AttributeOptionGrid(this.$('.attribute-option-grid'));

    this.renderExtensions();
  },
});
