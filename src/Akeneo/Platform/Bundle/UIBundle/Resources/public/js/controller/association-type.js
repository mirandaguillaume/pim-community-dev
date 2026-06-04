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
