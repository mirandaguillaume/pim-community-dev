'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('oro/translator');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
require('pim/dialog');
var PageTitle = __pimInterop(require('pim/page-title'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderForm: function (route) {
    return FetcherRegistry.getFetcher('attribute-group')
      .fetch(route.params.identifier, {cached: false})
      .then(attributeGroup => {
        if (!this.active) {
          return;
        }

        PageTitle.set({
          'group.label': i18n.getLabel(attributeGroup.labels, UserContext.get('catalogLocale'), attributeGroup.code),
        });

        return FormBuilder.build('pim-attribute-group-edit-form').then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(attributeGroup);

          form.trigger('pim_enrich:form:entity:post_fetch', attributeGroup);

          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
