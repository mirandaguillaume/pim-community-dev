'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var MultiselectField = __pimInterop(require('pim/multi-select-field'));
var Routing = __pimInterop(require('routing'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));

module.exports = MultiselectField.extend({
  fieldType: 'reference-multi-select',
  getTemplateContext: function () {
    return MultiselectField.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
      templateContext.userCanAddOption = false;

      return templateContext;
    });
  },
  getChoiceUrl: function () {
    return FetcherRegistry.getFetcher('reference-data-configuration')
      .fetchAll()
      .then(
        _.bind(function (config) {
          return Routing.generate('pim_ui_ajaxentity_list', {
            class: config[this.attribute.reference_data_name].class,
            dataLocale: this.context.locale,
            collectionId: this.attribute.meta.id,
            options: {type: 'code'},
          });
        }, this)
      );
  },
});
