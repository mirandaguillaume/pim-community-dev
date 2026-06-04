import 'oro/translator';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import 'pim/dialog';
import PageTitle from 'pim/page-title';
import * as i18n from 'pim/i18n';

export default BaseController.extend({
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
