import $ from 'jquery';
import translate from 'oro/translator';
import BaseRedirect from 'pim/common/redirect';
import FetcherRegistry from 'pim/fetcher-registry';
import router from 'pim/router';

export default BaseRedirect.extend({
  /**
   * {@inheritdoc}
   */
  render: function () {
    this.isVisible().then(isVisible => {
      this.$el.html(
        this.template({
          label: translate(this.config.label),
          buttonClass: `${this.config.buttonClass ?? 'AknButton--action'}${isVisible ? '' : ' AknButton--disabled'}`,
          title: isVisible ? '' : translate(this.config.title),
        })
      );
    });

    return this;
  },

  /**
   * Redirect to the route given in the config
   */
  redirect: function () {
    this.isVisible().then(isVisible => {
      isVisible && router.redirect(this.getUrl());
    });
  },

  /**
   * {@inheritdoc}
   */
  isVisible: function () {
    //If we are in CE, the permission registry does not exists so the button is visible
    if (undefined === FetcherRegistry.getFetcher('permission')?.options?.urls) return $.Deferred().resolve(true);

    return FetcherRegistry.getFetcher('permission')
      .fetchAll()
      .then(permissions => {
        const permission = permissions.job_instances.find(({code}) => this.getFormData().code === code);

        return permission?.edit ?? false;
      });
  },
});
