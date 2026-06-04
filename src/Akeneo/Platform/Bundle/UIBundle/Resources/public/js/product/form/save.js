import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import * as messenger from 'oro/messenger';
import ProductSaver from 'pim/saver/product';
import FieldManager from 'pim/field-manager';
import * as i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import analytics from 'pim/analytics';

export default BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.product.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.product.flash.update.fail'),
  updateIdentifierWarningMessage: __('pim_enrich.entity.product.flash.update.identifier_warning'),
  sessionExpiredMessage: __('pim_enrich.entity.product.flash.update.fail_session_expired'),

  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.save);
    this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

    return BaseSave.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  save: function (options) {
    var product = $.extend(true, {}, this.getFormData());
    var productUuid = product.meta.id;

    delete product.meta;

    var notReadyFields = FieldManager.getNotReadyFields();

    if (0 < notReadyFields.length) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(field.attribute.label, UserContext.get('catalogLocale'), field.attribute.code);
      });

      messenger.notify(
        'error',
        __('pim_enrich.entity.product.flash.update.fields_not_ready', {
          fields: fieldLabels.join(', '),
        })
      );

      return;
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    analytics.appcuesTrack('product:form:saved', {
      name: product.identifier,
    });

    return ProductSaver.save(productUuid, product)
      .fail(this.fail.bind(this))
      .then(
        function (data) {
          if (data.meta?.identifier_generator_warnings) {
            const normalizedWarnings = data.meta.identifier_generator_warnings.map(warning => {
              return warning.path ? `${warning.path}: ${warning.message} ` : warning.message;
            });

            messenger.notify('warning', this.updateIdentifierWarningMessage, normalizedWarnings);
          }
          this.postSave();

          this.setData(data, options);

          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
        }.bind(this)
      )
      .always(this.hideLoadingMask.bind(this));
  },
});
