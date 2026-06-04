import $ from 'jquery';
import 'underscore';
import __ from 'oro/translator';
import BaseLaunch from 'pim/job/common/edit/launch';
import router from 'pim/router';
import messenger from 'oro/messenger';
import LoadingMask from 'oro/loading-mask';

export default BaseLaunch.extend({
  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:job:file_updated', this.render.bind(this));

    return BaseLaunch.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inherit}
   */
  launch: function () {
    var loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo(this.getRoot().$el).show();

    if (this.getFormData().file) {
      var formData = new FormData();
      formData.append('file', this.getFormData().file);

      $.ajax({
        url: this.getUrl(),
        method: 'POST',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
      })
        .then(response => {
          if (response.redirectUrl) {
            router.redirect(response.redirectUrl);
          } else {
            router.reloadPage();
          }
        })
        .fail(response => {
          if (undefined !== response.responseJSON.message) {
            messenger.notify('error', __(response.responseJSON.message));
          } else {
            messenger.notify('error', __('pim_import_export.form.job_instance.fail.launch'));
          }
        })
        .always(() => {
          loadingMask.hide().$el.remove();
        });
    } else {
      loadingMask.hide().$el.remove();
    }
  },

  /**
   * {@inherit}
   */
  isVisible: function () {
    return $.Deferred().resolve(this.getFormData().file).promise();
  },
});
