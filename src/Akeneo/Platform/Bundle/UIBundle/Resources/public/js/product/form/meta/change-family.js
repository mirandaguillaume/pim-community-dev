import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';
import BaseForm from 'pim/form';
import innerModalTemplate from 'pim/template/product/meta/change-family-modal';
import Select2Configurator from 'pim/common/select2/family';
import initSelect2 from 'pim/initselect2';
import 'bootstrap-modal';
import 'jquery.select2';

export default BaseForm.extend({
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
