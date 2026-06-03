'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var Backbone = __pimInterop(require('backbone'));
var Routing = __pimInterop(require('routing'));
var FormBuilder = __pimInterop(require('pim/form-builder'));
var messenger = __pimInterop(require('oro/messenger'));
var errorTemplate = __pimInterop(require('pim/template/attribute-option/validation-error'));
var CreateOptionView = Backbone.View.extend({
  errorTemplate: _.template(errorTemplate),
  attribute: null,

  initialize: function (options) {
    this.attribute = options.attribute;
  },
  createOption: function () {
    var deferred = $.Deferred();

    FormBuilder.build('pim-attribute-option-form').done(form => {
      var modal = new Backbone.BootstrapModal({
        title: _.__('pim_enrich.entity.product.module.attribute.add_attribute_option'),
        content: form,
        cancelText: _.__('pim_common.cancel'),
        okText: _.__('pim_common.add'),
        picture: 'illustrations/Attribute.svg',
        okCloses: false,
      });
      modal.open();

      modal.on('cancel', deferred.reject);
      modal.on('ok', () => {
        $.ajax({
          method: 'POST',
          url: Routing.generate('pim_enrich_attributeoption_create', {attributeId: this.attribute.meta.id}),
          data: JSON.stringify(form.getFormData()),
        })
          .done(option => {
            modal.close();
            messenger.notify('success', _.__('pim_enrich.entity.attribute_option.flash.create.success'));
            deferred.resolve(option);
          })
          .fail(xhr => {
            var response = xhr.responseJSON;

            if (response.code) {
              form.$('input[name="code"]').after(
                this.errorTemplate({
                  errors: [response.code],
                })
              );
            } else {
              messenger.notify('error', _.__('pim_enrich.entity.attribute_option.flash.create.fail'));
            }
          });
      });
    });

    return deferred.promise();
  },
});

module.exports = function (attribute) {
  if (!attribute) {
    throw new Error('Attribute must be provided to create a new option');
  }

  var view = new CreateOptionView({attribute: attribute});

  return view.createOption().always(function () {
    view.remove();
  });
};
