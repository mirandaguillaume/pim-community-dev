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
    return FetcherRegistry.getFetcher('family')
      .fetch(route.params.code, {cached: false, full_attributes: false})
      .then(family => {
        if (!this.active) {
          return;
        }

        PageTitle.set({'family.label': i18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code)});

        return FormBuilder.build(family.meta.form).then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(family);
          form.trigger('pim_enrich:form:entity:post_fetch', family);
          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
