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
    FetcherRegistry.getFetcher('locale').clear();
    if (undefined === route.params.code) {
      var label = 'pim_enrich.entity.channel.label.create';

      return createForm.call(
        this,
        this.$el,
        {
          code: '',
          currencies: [],
          locales: [],
          category_tree: '',
          conversion_units: [],
          labels: {},
          meta: {},
        },
        label,
        'pim-channel-create-form'
      );
    } else {
      return FetcherRegistry.getFetcher('channel')
        .fetch(route.params.code, {cached: false, filter_locales: 0})
        .then(channel => {
          const label = i18n.getLabel(channel.labels, UserContext.get('catalogLocale'), channel.code);

          return createForm.call(this, this.$el, channel, label, channel.meta.form);
        });
    }

    function createForm(domElement, channel, label, formExtension) {
      PageTitle.set({'channel.label': label});

      return FormBuilder.build(formExtension).then(form => {
        this.on('pim:controller:can-leave', function (event) {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setData(channel);
        form.trigger('pim_enrich:form:entity:post_fetch', channel);
        form.setElement(domElement).render();

        return form;
      });
    }
  },
});
