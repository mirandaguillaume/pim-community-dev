import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import messenger from 'oro/messenger';
import BaseForm from 'pim/form';
import FormModal from 'pim/form-modal';

export default BaseForm.extend({
  className: 'AknButton AknButton--action AknButton--small add-variant',
  modal: null,

  events: {
    click: 'openModal',
  },

  /**
   * {@inheritdoc}
   */
  render() {
    this.$el.html(__('pim_enrich.entity.family_variant.module.create.label'));
    this.delegateEvents();

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * Open the modal containing the form to create a new family variant.
   */
  openModal() {
    const modalParameters = {
      className: 'modal modal--fullPage add-family-variant-modal',
      content: '',
      cancelText: __('pim_common.cancel'),
      okText: __('pim_common.create'),
      okCloses: false,
    };

    const formModal = new FormModal('pim-family-variant-create-form', this.submitForm.bind(this), modalParameters, {
      family: this.getFormData().code,
    });

    formModal.open();
  },

  /**
   * Action made when user submit the modal.
   */
  submitForm(formModal) {
    return formModal.saveFamilyVariant().then(familyVariant => {
      messenger.notify('success', _.__('pim_enrich.entity.family_variant.flash.create.success'));
      this.getRoot().trigger('pim_enrich.entity.family.family_variant.post_create', familyVariant);
    });
  },
});
