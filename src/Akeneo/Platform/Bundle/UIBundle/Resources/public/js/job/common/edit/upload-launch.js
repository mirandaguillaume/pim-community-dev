'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
var __ = __pimInterop(require('oro/translator'));
var BaseLaunch = __pimInterop(require('pim/job/common/edit/launch'));
var router = __pimInterop(require('pim/router'));
var messenger = __pimInterop(require('oro/messenger'));
var LoadingMask = __pimInterop(require('oro/loading-mask'));

module.exports = BaseLaunch.extend({
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
