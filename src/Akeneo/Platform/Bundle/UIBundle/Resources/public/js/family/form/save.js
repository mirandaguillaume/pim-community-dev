'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseSave = __pimInterop(require('pim/form/common/save'));
var messenger = __pimInterop(require('oro/messenger'));
var FamilySaver = __pimInterop(require('pim/saver/family'));
var FieldManager = __pimInterop(require('pim/field-manager'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
require('pim/security-context');

module.exports = BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.family.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.family.flash.update.fail'),

  /**
   * {@inheritdoc}
   */
  save: function () {
    var family = $.extend(true, {}, this.getFormData());
    family.attributes = _.pluck(family.attributes, 'code');

    delete family.meta;

    var notReadyFields = FieldManager.getNotReadyFields();
    if (0 < notReadyFields.length) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(field.attribute.label, UserContext.get('catalogLocale'), field.attribute.code);
      });

      messenger.notify(
        'error',
        __('pim_enrich.entity.family.flash.update.fields_not_ready', {fields: fieldLabels.join(', ')})
      );

      return;
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return FamilySaver.save(family.code, family, 'PUT')
      .then(
        function (data) {
          this.postSave();

          this.setData(data);
          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },
});
