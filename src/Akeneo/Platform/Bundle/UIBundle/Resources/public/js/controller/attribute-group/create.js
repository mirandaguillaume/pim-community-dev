'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderForm: function () {
    if (!this.active) {
      return;
    }

    return FormBuilder.build('pim-attribute-group-create-form').then(form => {
      this.on('pim:controller:can-leave', function (event) {
        form.trigger('pim_enrich:form:can-leave', event);
      });
      form.setData({
        code: '',
        labels: {},
      });

      form.setElement(this.$el).render();

      return form;
    });
  },
});
