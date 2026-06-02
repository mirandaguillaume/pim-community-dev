'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseSave = __pimInterop(require('pim/form/common/save'));
var messenger = __pimInterop(require('oro/messenger'));
var ProductModelSaver = __pimInterop(require('pim/saver/product-model'));
var FieldManager = __pimInterop(require('pim/field-manager'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseSave.extend({
  updateSuccessMessage: __('pim_enrich.entity.product_model.flash.update.success'),
  updateFailureMessage: __('pim_enrich.entity.product_model.flash.update.fail'),

  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

    return BaseSave.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  save: function (options) {
    var productModel = $.extend(true, {}, this.getFormData());
    var productModelId = productModel.meta.id;

    delete productModel.meta;
    delete productModel.family;

    var notReadyFields = FieldManager.getNotReadyFields();

    if (0 < notReadyFields.length) {
      var fieldLabels = _.map(notReadyFields, function (field) {
        return i18n.getLabel(field.attribute.label, UserContext.get('catalogLocale'), field.attribute.code);
      });

      messenger.notify(
        'error',
        __('pim_enrich.entity.product_model.flash.update.fields_not_ready', {
          fields: fieldLabels.join(', '),
        })
      );

      return;
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    analytics.appcuesTrack('product-model:form:saved', {
      code: productModel.code,
    });

    return ProductModelSaver.save(productModelId, productModel)
      .then(
        function (data) {
          this.postSave();

          this.setData(data, options);

          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },
});
