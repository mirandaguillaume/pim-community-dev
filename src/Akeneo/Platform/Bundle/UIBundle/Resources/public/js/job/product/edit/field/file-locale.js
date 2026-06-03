'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
require('underscore');
require('oro/translator');
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var BaseField = __pimInterop(require('pim/job/common/edit/field/field'));
var SelectField = __pimInterop(require('pim/job/common/edit/field/select'));
var UserContext = __pimInterop(require('pim/user-context'));

module.exports = SelectField.extend({
  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'job.with_label.change', () => {
      this.render();
    });

    return $.when(
      FetcherRegistry.getFetcher('locale').fetchActivated(),
      SelectField.prototype.configure.apply(this, arguments)
    ).then(
      function (locales) {
        this.config.options = locales.reduce((result, locale) => ({...result, [locale.code]: locale.label}), {});
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.getFormData().configuration.with_label) {
      this.$el.html('');

      return this;
    }

    BaseField.prototype.render.apply(this, arguments);

    const select2 = this.$('.select2');
    select2.select2();

    const fileLocale = this.getFormData().configuration.file_locale;
    if (undefined === fileLocale || null === fileLocale) {
      select2.val(UserContext.get('catalogLocale')).change();
    }
  },
});
