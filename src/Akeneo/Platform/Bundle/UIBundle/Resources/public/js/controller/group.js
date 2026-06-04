import 'oro/translator';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import 'pim/dialog';
import PageTitle from 'pim/page-title';
import * as i18n from 'pim/i18n';

export default BaseController.extend({
  initialize: function () {
    this.config = __moduleConfig;
  },

  /**
   * {@inheritdoc}
   */
  renderForm: function (route) {
    return FetcherRegistry.getFetcher(this.config.fetcher)
      .fetch(route.params.code, {cached: false})
      .then(group => {
        if (!this.active) {
          return;
        }

        PageTitle.set({'group.label': i18n.getLabel(group.labels, UserContext.get('catalogLocale'), group.code)});

        return FormBuilder.build(group.meta.form).then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(group);
          form.trigger('pim_enrich:form:entity:post_fetch', group);
          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
