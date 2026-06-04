import 'oro/translator';
import BaseController from 'pim/controller/front';
import FormBuilder from 'pim/form-builder';
import FetcherRegistry from 'pim/fetcher-registry';
import 'pim/user-context';
import 'pim/dialog';
import PageTitle from 'pim/page-title';

export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderForm: function (route) {
    var type = route.name.indexOf('pim_importexport_import') === -1 ? 'export' : 'import';
    var mode = route.name.indexOf('_profile_show') === -1 ? 'edit' : 'show';

    return FetcherRegistry.getFetcher('job-instance-' + type)
      .fetch(route.params.code, {cached: false})
      .then(jobInstance => {
        if (!this.active) {
          return;
        }

        PageTitle.set({'job.label': jobInstance.label});

        return FormBuilder.build(jobInstance.meta.form + '-' + mode).then(form => {
          this.on('pim:controller:can-leave', event => {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setData(jobInstance);
          form.trigger('pim_enrich:form:entity:post_fetch', jobInstance);
          form.setElement(this.$el).render();

          return form;
        });
      });
  },
});
