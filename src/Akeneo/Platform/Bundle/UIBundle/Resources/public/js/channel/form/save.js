import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import ChannelSaver from 'pim/saver/channel';
import 'pim/field-manager';
import 'pim/i18n';
import 'pim/user-context';
import 'routing';
import router from 'pim/router';

export default BaseSave.extend({
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
