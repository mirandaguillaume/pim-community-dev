'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('oro/translator');
var BaseController = __pimInterop(require('pim/controller/front'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
require('pim/user-context');
require('pim/dialog');
var PageTitle = __pimInterop(require('pim/page-title'));

module.exports = BaseController.extend({
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
