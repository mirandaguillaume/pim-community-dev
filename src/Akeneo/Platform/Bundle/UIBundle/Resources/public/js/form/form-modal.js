import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';
import mediator from 'oro/mediator';
import FormBuilder from 'pim/form-builder';

export default Backbone.View.extend({
  /**
   * The form name the modal should display.
   * This service must be registered with RequireJS, eg: 'pim-product-edit-form'
   */
  formName: '',

  /**
   * Instance of the UI modal element.
   */
  modal: null,

  /**
   * Callback triggered on form submission.
   * This callback should return a promise, resolved when data validation check is OK.
   */
  submitCallback: null,

  /**
   * UI modal parameters
   */
  modalParameters: {
    okCloses: false,
    content: '',
    title: '[modal_title]',
    okText: __('pim_common.save'),
    modalOptions: {
      backdrop: 'static',
      keyboard: false,
    },
  },

  /**
   * Initial form data of the modal
   */
  initialFormData: {},

  /**
   * @param {string}   formName
   * @param {function} submitCallback
   * @param {Object}   modalParameters
   * @param {Object}   initialFormData
   */
  initialize: function (formName, submitCallback, modalParameters, initialFormData = {}) {
    this.formName = formName;
    this.submitCallback = submitCallback;
    this.modalParameters = _.extend(this.modalParameters, modalParameters);
    this.initialFormData = initialFormData;
  },

  /**
   * Render the modal with the custom form service.
   * Returns the deferred object to catch success (OK) & fail (Cancel) event of the modal.
   *
   * @return {Promise}
   */
  open: function () {
    var deferred = $.Deferred();

    FormBuilder.build(this.formName).then(
      function (form) {
        form.setData(this.initialFormData, {silent: true});

        this.modal = new Backbone.BootstrapModal(this.modalParameters);
        this.modal.open();
        form.setElement(this.modal.$('.modal-body')).render();

        mediator.on(
          'pim_enrich:form:modal:ok_button:disable',
          function () {
            this.disableOkBtn();
          }.bind(this)
        );

        mediator.on(
          'pim_enrich:form:modal:ok_button:enable',
          function () {
            this.enableOkBtn();
          }.bind(this)
        );

        this.modal.on('cancel', deferred.reject);
        this.modal.on(
          'ok',
          function () {
            if (this.modal.$('.modal-footer .ok').hasClass('disabled')) {
              return;
            }
            this.submitCallback(form).then(
              function () {
                var data = form.getFormData();
                deferred.resolve(data);

                this.modal.close();
              }.bind(this)
            );
          }.bind(this)
        );
      }.bind(this)
    );

    return deferred;
  },

  /**
   * Close the modal UI element.
   */
  close: function () {
    this.modal.close();
  },

  /**
   * Enable the modal ok button.
   */
  enableOkBtn: function () {
    this.modal.$('.ok').attr('disabled', null).removeClass('AknButton--disabled');
  },

  /**
   * Disable the modal ok button.
   */
  disableOkBtn: function () {
    this.modal.$('.ok').attr('disabled', 'disabled').addClass('AknButton--disabled');
  },
});
