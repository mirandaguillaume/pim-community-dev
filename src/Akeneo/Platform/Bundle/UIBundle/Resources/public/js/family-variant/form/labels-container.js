import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/family-variant/labels-container';

export default BaseForm.extend({
  render: function () {
    this.$el.html(
      _.template(template)({
        __: __,
        familyVariant: this.getFormData(),
      })
    );

    this.renderExtensions();
  },
});
