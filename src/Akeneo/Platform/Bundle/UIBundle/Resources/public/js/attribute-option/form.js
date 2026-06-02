'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
require('backbone');
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/attribute-option/form'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseForm.extend({
  template: _.template(template),
  events: {
    'change input': 'updateModel',
  },
  updateModel: function () {
    var optionValues = {};

    _.each(this.$('input[name^="label-"]'), function (labelInput) {
      var locale = labelInput.dataset.locale;
      optionValues[locale] = {
        locale: locale,
        value: labelInput.value,
      };
    });

    this.getFormModel().set('code', this.$('input[name="code"]').val());
    this.getFormModel().set('optionValues', optionValues);
  },
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(
      this.template({
        locale: UserContext.get('catalogLocale'),
        i18n: i18n,
        option: this.getFormData(),
        __,
      })
    );

    return this.renderExtensions();
  },
});
