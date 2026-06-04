import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';
import fetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import PageTitle from 'pim/page-title';
import * as i18n from 'pim/i18n';

export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderForm: function (route) {
    if (!this.active) {
      return;
    }

    fetcherRegistry.getFetcher('attribute-group').clear();
    fetcherRegistry.getFetcher('locale').clear();
    fetcherRegistry.getFetcher('measure').clear();

    return fetcherRegistry
      .getFetcher('attribute')
      .fetch(route.params.code, {
        cached: false,
        apply_filters: false,
      })
      .then(attribute => {
        var label = i18n.getLabel(attribute.labels, UserContext.get('catalogLocale'), attribute.code);

        PageTitle.set({'attribute.label': label});

        return FormBuilder.getFormMeta('pim-attribute-edit-form')
          .then(FormBuilder.buildForm)
          .then(form => {
            form.setType(attribute.type);

            return form.configure().then(() => {
              return form;
            });
          })
          .then(form => {
            this.on('pim:controller:can-leave', function (event) {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setData(attribute);
            form.trigger('pim_enrich:form:entity:post_fetch', attribute);
            form.setElement(this.$el).render();

            return form;
          });
      });
  },
});
