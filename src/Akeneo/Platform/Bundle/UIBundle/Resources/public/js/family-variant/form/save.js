'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseSave = __pimInterop(require('pim/form/common/save'));
var messenger = __pimInterop(require('oro/messenger'));
var FamilyVariantSaver = __pimInterop(require('pim/saver/family-variant'));
var FieldManager = __pimInterop(require('pim/field-manager'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var mediator = __pimInterop(require('oro/mediator'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.family_variant.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.family_variant.flash.update.fail'),

  /**
   * {@inheritdoc}
   */
  save: function () {
    var familyVariant = $.extend(true, {}, this.getFormData());

    delete familyVariant.meta;

    var notReadyFields = FieldManager.getNotReadyFields();
    if (0 < notReadyFields.length) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(field.attribute.label, UserContext.get('catalogLocale'), field.attribute.code);
      });

      messenger.notify(
        'error',
        __('pim_enrich.entity.family_variant.info.field_not_ready', {fields: fieldLabels.join(', ')})
      );

      return;
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return FamilyVariantSaver.save(familyVariant.code, familyVariant, 'PUT')
      .then(
        function (data) {
          this.postSave();

          this.setData(data);
          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
          this.getRoot().trigger('pim_enrich:form:entity:post_save', data);
          mediator.trigger('datagrid:doRefresh:family-variant-grid');

          analytics.appcuesTrack('family:variant:saved');
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },
  fail: function (response) {
    switch (response.status) {
      case 422:
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

    const responseJSON = response.responseJSON;

    if (Array.isArray(responseJSON)) {
      for (const errorPayload of responseJSON) {
        messenger.notify('error', errorPayload.message);
      }
    } else {
      messenger.notify('error', this.updateFailureMessage);
    }
  },
});
