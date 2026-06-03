'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var i18n = __pimInterop(require('pim/i18n'));
var UserContext = __pimInterop(require('pim/user-context'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/family-variant/add-variant-form-header'));

module.exports = BaseForm.extend({
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
