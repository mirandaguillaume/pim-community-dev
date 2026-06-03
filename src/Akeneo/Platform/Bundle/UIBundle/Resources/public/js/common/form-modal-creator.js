'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Backbone = __pimInterop(require('backbone'));
var formBuilder = __pimInterop(require('pim/form-builder'));
var fetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var modalTemplate = __pimInterop(require('pim/template/common/modal-centered'));

module.exports = {
  /**
   * Create a modal from fetcher and entity identifier
   *
   * @param {String} entityCode
   * @param {String} fetcherCode
   *
   * @return {Promise}
   */
  createModal: function (entityCode, fetcherCode) {
    return fetcherRegistry
      .getFetcher(fetcherCode)
      .fetch(entityCode, {cached: false})
      .then(entity => {
        return formBuilder.build(entity.meta.form).then(form => {
          form.setData(entity);
          form.trigger('pim_enrich:form:entity:post_fetch', entity);
          form.on('pim_enrich:form:entity:post_save', () => {
            modal.trigger('cancel');
          });

          const familyVariant = entity;
          const modal = new Backbone.BootstrapModal({
            content: form,
            buttons: false,
            title: i18n.getLabel(familyVariant.labels, UserContext.get('catalogLocale'), familyVariant.code),
            subtitle: __('Code') + ': ' + familyVariant.code,
            template: _.template(modalTemplate),
            okText: '',
            innerClassName: 'AknFullPage--full',
          });
          modal.open();

          return modal;
        });
      });
  },
};
