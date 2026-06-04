import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseField from 'pim/form/common/fields/field';
import fetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import i18n from 'pim/i18n';
import template from 'pim/template/attribute/tab/properties/group';

export default BaseField.extend({
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
