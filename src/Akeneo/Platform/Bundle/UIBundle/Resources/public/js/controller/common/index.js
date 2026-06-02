'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));

module.exports = BaseController.extend({
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
