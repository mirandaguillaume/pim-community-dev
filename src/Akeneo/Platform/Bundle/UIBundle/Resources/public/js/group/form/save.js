'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseSave = __pimInterop(require('pim/form/common/save'));
var messenger = __pimInterop(require('oro/messenger'));
var GroupSaver = __pimInterop(require('pim/saver/group'));
var FieldManager = __pimInterop(require('pim/field-manager'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.group.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.group.flash.update.fail'),

  /**
   * {@inheritdoc}
   */
  save: function () {
    var group = $.extend(true, {}, this.getFormData());

    delete group.meta;

    var notReadyFields = FieldManager.getNotReadyFields();

    if (0 < notReadyFields.length) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(field.attribute.label, UserContext.get('catalogLocale'), field.attribute.code);
      });

      messenger.notify(
        'error',
        __('pim_enrich.entity.group.flash.update.fields_not_ready', {fields: fieldLabels.join(', ')})
      );

      return;
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return GroupSaver.save(group.code, group)
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
