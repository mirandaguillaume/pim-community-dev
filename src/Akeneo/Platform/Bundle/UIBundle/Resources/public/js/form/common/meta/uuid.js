import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import formTemplate from 'pim/template/form/meta/uuid';

export default BaseForm.extend({
  tagName: 'span',
  className: 'AknTitleContainer-metaItem',
  template: _.template(formTemplate),

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = meta.config;
    this.label = __(this.config.label);

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    var product = this.getFormData();
    var html = this.template({
      label: this.label,
      uuid: _.result(product.meta, 'uuid', null),
    });

    this.$el.html(html);

    return this;
  },
});
