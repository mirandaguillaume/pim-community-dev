import _ from 'underscore';
import __ from 'oro/translator';
import * as i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import FetcherRegistry from 'pim/fetcher-registry';
import BaseForm from 'pim/form';
import template from 'pim/template/family-variant/add-variant-form-header';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  render() {
    const catalogLocal = UserContext.get('catalogLocale');

    FetcherRegistry.getFetcher('family')
      .fetch(this.getFormData().family)
      .then(family => {
        this.$el.html(
          this.template({
            __: __,
            familyName: i18n.getLabel(family.labels, catalogLocal, family.code),
          })
        );
      });
  },
});
