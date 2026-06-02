'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseSave = __pimInterop(require('pim/form/common/save'));
var messenger = __pimInterop(require('oro/messenger'));
var ChannelSaver = __pimInterop(require('pim/saver/channel'));
require('pim/field-manager');
require('pim/i18n');
require('pim/user-context');
require('routing');
var router = __pimInterop(require('pim/router'));

module.exports = BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.channel.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.channel.flash.update.fail'),
  createSuccessMessage: __('pim_enrich.entity.channel.flash.create.success'),
  createFailureMessage: __('pim_enrich.entity.channel.flash.create.fail'),

  /**
   * {@inheritdoc}
   */
  postSave: function (isUpdate) {
    this.getRoot().trigger('pim_enrich:form:entity:post_save');
    var code = this.getFormData().code;
    if (!isUpdate) {
      messenger.notify('success', this.createSuccessMessage);
      router.redirectToRoute(this.config.redirectUrl, {code: code});

      return;
    }

    messenger.notify('success', this.updateSuccessMessage);
  },

  /**
   * {@inheritdoc}
   */
  save: function () {
    var channel = $.extend(true, {}, this.getFormData());
    var code = null;
    var isUpdate = false;
    var method = 'POST';

    if (_.has(channel.meta, 'id')) {
      code = channel.code;
      isUpdate = true;
      method = 'PUT';
    }

    delete channel.meta;

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return ChannelSaver.save(code, channel, method)
      .then(
        function (data) {
          this.setData(data);
          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
          this.postSave(isUpdate);
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },
});
