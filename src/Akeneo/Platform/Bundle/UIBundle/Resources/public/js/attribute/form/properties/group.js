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
var __ = __pimInterop(require('oro/translator'));
var BaseField = __pimInterop(require('pim/form/common/fields/field'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var template = __pimInterop(require('pim/template/attribute/tab/properties/group'));

module.exports = BaseField.extend({
  events: {
    'change select': function (event) {
      this.errors = [];
      this.updateModel(this.getFieldValue(event.target));
      this.getRoot().render();
    },
  },
  template: _.template(template),
  attributeGroups: {},

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      BaseField.prototype.configure.apply(this, arguments),
      fetcherRegistry
        .getFetcher('attribute-group')
        .fetchAll()
        .then(
          function (attributeGroups) {
            this.attributeGroups = attributeGroups;
          }.bind(this)
        )
    );
  },

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(
      _.extend(templateContext, {
        value: this.getFormData()[this.fieldName],
        groups: _.sortBy(this.attributeGroups, 'sort_order'),
        i18n: i18n,
        locale: UserContext.get('catalogLocale'),
        labels: {
          defaultLabel: __('pim_enrich.entity.attribute.property.group.choose'),
        },
      })
    );
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('select.select2').select2();
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val();
  },
});
