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
    return FetcherRegistry.getFetcher('group-type')
      .fetch(route.params.code, {cached: false})
      .then(groupType => {
        if (!this.active) {
          return;
        }

        PageTitle.set({
          'group type.label': i18n.getLabel(groupType.labels, UserContext.get('catalogLocale'), groupType.code),
        });

        return FormBuilder.build('pim-group-type-edit-form').then(form => {
          this.on('pim:controller:can-leave', event => {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(groupType);
          form.trigger('pim_enrich:form:entity:post_fetch', groupType);
          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
