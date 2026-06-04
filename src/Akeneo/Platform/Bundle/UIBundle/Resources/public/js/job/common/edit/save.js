import $ from 'jquery';
import 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import 'pim/field-manager';
import 'pim/i18n';
import 'pim/user-context';
import 'routing';
import router from 'pim/router';
import analytics from 'pim/analytics';

export default BaseSave.extend({
  updateSuccessMessage: __('pim_import_export.entity.job_instance.flash.update.success'),
  updateFailureMessage: __('pim_import_export.entity.job_instance.flash.update.fail'),

  /**
   * {@inheritdoc}
   */
  save: function () {
    var jobInstance = $.extend(true, {}, this.getFormData());

    delete jobInstance.meta;
    delete jobInstance.connector;

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return this.getJobInstanceSaver()
      .save(jobInstance.code, jobInstance)
      .then(
        function (data) {
          this.postSave();

          this.setData(data);
          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);

          analytics.appcuesTrack('job-instance:form-edit:saved', {
            code: jobInstance.code,
          });

          router.redirectToRoute(this.config.redirectPath, {code: jobInstance.code});
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },

  /**
   * @inheritDoc
   */
  fail: function (response) {
    switch (response.status) {
      case 400:
        this.getRoot().trigger('pim_enrich:form:entity:bad_request', {
          sentData: this.getFormData(),
          response: response.responseJSON,
        });
        break;
      case 500:
        /* global console */
        const message = response.responseJSON ? response.responseJSON : response;

        console.error('Errors:', message);
        this.getRoot().trigger('pim_enrich:form:entity:error:save', message);
        break;
      default:
    }

    messenger.notify(
      'error',
      response.responseJSON.message ? __(response.responseJSON.message) : this.updateFailureMessage
    );
  },
});
