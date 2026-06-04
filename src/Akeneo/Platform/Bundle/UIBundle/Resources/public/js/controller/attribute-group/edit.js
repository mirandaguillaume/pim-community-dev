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
    return FetcherRegistry.getFetcher('attribute-group')
      .fetch(route.params.identifier, {cached: false})
      .then(attributeGroup => {
        if (!this.active) {
          return;
        }

        PageTitle.set({
          'group.label': i18n.getLabel(attributeGroup.labels, UserContext.get('catalogLocale'), attributeGroup.code),
        });

        return FormBuilder.build('pim-attribute-group-edit-form').then(form => {
          this.on('pim:controller:can-leave', function (event) {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(attributeGroup);

          form.trigger('pim_enrich:form:entity:post_fetch', attributeGroup);

          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
