import 'underscore';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';

export default BaseController.extend({
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
