import 'underscore';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';

export default BaseController.extend({
  initialize: function (options) {
    this.options = options;
  },

  /**
   * {@inheritdoc}
   */
  renderForm: function () {
    return FormBuilder.build('pim-' + this.options.config.entity + '-index').then(form => {
      form.setElement(this.$el).render();

      return form;
    });
  },
});
