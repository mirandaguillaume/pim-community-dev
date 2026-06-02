function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
require('jquery');
var FormBuilder = __pimInterop(require('pim/form-builder'));
var BaseForm = __pimInterop(require('pim/form'));

module.exports = BaseForm.extend({
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
