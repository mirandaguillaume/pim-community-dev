import 'jquery';
import 'underscore';
import 'backbone';
import 'oro/translator';
import BaseForm from 'pim/form';
import FormBuilder from 'pim/form-builder';
import FormRegistry from 'pim/attribute-edit-form/type-specific-form-registry';

export default BaseForm.extend({
  config: {},

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    var formName = FormRegistry.getFormName(this.getRoot().getType(), this.config.mode);

    if (undefined !== formName && null !== formName) {
      return FormBuilder.getFormMeta(formName)
        .then(FormBuilder.buildForm)
        .then(
          function (form) {
            this.addExtension(form.code, form, 'self', 100);

            return BaseForm.prototype.configure.apply(this);
          }.bind(this)
        );
    }

    return BaseForm.prototype.configure.apply(this);
  },
});
