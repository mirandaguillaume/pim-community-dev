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
