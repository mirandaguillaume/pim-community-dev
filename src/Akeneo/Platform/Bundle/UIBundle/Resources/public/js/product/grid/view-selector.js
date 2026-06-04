import 'underscore';
import 'jquery';
import FormBuilder from 'pim/form-builder';
import BaseForm from 'pim/form';

export default BaseForm.extend({
  className: 'view-selector',
  config: {
    gridName: 'product-grid',
  },

  /**
   * {@inheritdoc}
   */
  initialize(options) {
    this.config = Object.assign(this.config, options.config || {});

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render() {
    FormBuilder.getFormMeta('pim-grid-view-selector')
      .then(FormBuilder.buildForm)
      .then(form => {
        return form.configure(this.config.gridName).then(() => {
          form.setElement('.view-selector').render();
        });
      });
  },
});
