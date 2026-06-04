import BaseModal from 'pim/form/common/creation/modal';

export default BaseModal.extend({
  events: {
    'keyup input': 'updateButtonState',
  },

  updateButtonState() {
    this.$el.parent().find('.AknButton.ok').toggleClass('AknButton--disabled', !this.isReadyToSubmit());
  },

  isReadyToSubmit() {
    const data = this.getFormData();

    return !Object.keys(this.extensions).some(extensionKey => {
      const extension = this.getExtension(extensionKey);

      return extension.config.required && (undefined === data[extension.fieldName] || '' === data[extension.fieldName]);
    });
  },

  /**
   * {@inheritdoc}
   */
  render() {
    BaseModal.prototype.render.apply(this, arguments);
    this.updateButtonState();

    return this;
  },

  /**
   * {@inheritdoc}
   */
  confirmModal() {
    if (!this.isReadyToSubmit()) return;

    return BaseModal.prototype.confirmModal.apply(this, arguments);
  },
});
