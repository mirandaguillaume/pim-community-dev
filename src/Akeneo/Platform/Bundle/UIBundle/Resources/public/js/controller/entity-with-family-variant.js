'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('underscore');
require('oro/translator');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
require('pim/dialog');
var PageTitle = __pimInterop(require('pim/page-title'));

module.exports = BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderForm: function (route) {
    return FetcherRegistry.getFetcher(this.options.config.entity)
      .fetch(route.params.uuid || route.params.id, {cached: false})
      .then(product => {
        if (!this.active) {
          return;
        }

        PageTitle.set({'product.label': product.meta.label[UserContext.get('catalogLocale')]});

        return FormBuilder.build(product.meta.form).then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(product);

          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
