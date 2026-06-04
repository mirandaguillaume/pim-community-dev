import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import FamilySaver from 'pim/saver/family';
import FieldManager from 'pim/field-manager';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import 'pim/security-context';

export default BaseSave.extend({
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
