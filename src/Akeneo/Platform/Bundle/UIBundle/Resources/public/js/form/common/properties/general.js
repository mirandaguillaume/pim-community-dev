import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import 'pim/fetcher-registry';
import propertyAccessor from 'pim/common/property';
import template from 'pim/template/form/properties/general';
import 'jquery.select2';

export default BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    var config = this.options.config;

    this.$el.html(
      this.template({
        model: this.getFormData(),
        sectionTitle: __(config.sectionTitle),
        codeLabel: __(config.codeLabel),
        formRequired: __(config.formRequired),
        inputField: config.inputField,
        hasId: propertyAccessor.accessProperty(this.getFormData(), 'meta.id') !== null,
      })
    );

    this.$el.find('select.select2').select2({});

    this.renderExtensions();
  },
});
