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
    return FetcherRegistry.getFetcher('association-type')
      .fetch(route.params.code, {cached: false})
      .then(associationType => {
        if (!this.active) {
          return;
        }

        PageTitle.set({
          'association type.label': i18n.getLabel(
            associationType.labels,
            UserContext.get('catalogLocale'),
            associationType.code
          ),
        });

        return FormBuilder.build(associationType.meta.form).then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(associationType);
          form.trigger('pim_enrich:form:entity:post_fetch', associationType);
          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
