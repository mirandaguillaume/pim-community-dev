import 'underscore';
import 'oro/translator';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';
import FetcherRegistry from 'pim/fetcher-registry';
import UserContext from 'pim/user-context';
import 'pim/dialog';
import PageTitle from 'pim/page-title';

export default BaseController.extend({
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
