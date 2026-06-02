'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var Backbone = __pimInterop(require('backbone'));
var BaseForm = __pimInterop(require('pim/form'));
var innerModalTemplate = __pimInterop(require('pim/template/product/meta/change-family-modal'));
var Select2Configurator = __pimInterop(require('pim/common/select2/family'));
var initSelect2 = __pimInterop(require('pim/initselect2'));
require('bootstrap-modal');
require('jquery.select2');

module.exports = BaseForm.extend({
  className: 'AknColumn-blockDown change-family',
  innerModalTemplate: _.template(innerModalTemplate),
  events: {
    click: 'showModal',
  },
  render: function () {
    if (null !== this.getFormData().meta.family_variant) {
      this.$el.remove();

      return;
    }

    this.delegateEvents();

    return BaseForm.prototype.render.apply(this, arguments);
  },
  showModal: function () {
    var familyModal = new Backbone.BootstrapModal({
      title: __('pim_enrich.entity.product.module.change_family.title'),
      content: this.innerModalTemplate({
        product: this.getFormData(),
      }),
      illustrationClass: 'families',
      okText: __('pim_common.save'),
      cancelText: __('pim_common.cancel'),
      innerDescription:
        __('pim_enrich.entity.product.module.change_family.merge_attributes') +
        ' ' +
        __('pim_enrich.entity.product.module.change_family.keep_attributes'),
    });

    familyModal.on(
      'ok',
      function () {
        var selectedFamily = familyModal.$('.family-select2').select2('val') || null;

        this.getRoot().trigger('pim_enrich:form:change-family:before');

        this.setData({family: selectedFamily});
        familyModal.close();

        this.getRoot().trigger('pim_enrich:form:change-family:after');
      }.bind(this)
    );

    familyModal.open();

    var options = Select2Configurator.getConfig(this.getFormData().family);

    initSelect2.init(familyModal.$('.family-select2'), options).select2('val', []);
  },
});
