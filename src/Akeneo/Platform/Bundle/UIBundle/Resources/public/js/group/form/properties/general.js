import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import 'pim/fetcher-registry';
import template from 'pim/template/group/tab/properties/general';
import 'jquery.select2';

export default BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        model: this.getFormData(),
        sectionTitle: __('pim_common.general_properties'),
        codeLabel: __('pim_common.code'),
        typeLabel: __('pim_common.type'),
        __: __,
      })
    );

    this.$el.find('select.select2').select2({});

    this.renderExtensions();
  },
});
