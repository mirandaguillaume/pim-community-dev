'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var messenger = __pimInterop(require('oro/messenger'));
var BaseOperation = __pimInterop(require('pim/mass-edit-form/product/operation'));
var propertyAccessor = __pimInterop(require('pim/common/property'));

var template = __pimInterop(require('pim/template/mass-edit/product/update-entity-with-family-variant-parent'));

module.exports = BaseOperation.extend({
  template: _.template(template),
  events: {},

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.updateModel);

    return BaseOperation.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        value: this.getValue(),
        readOnly: this.readOnly,
      })
    );

    BaseOperation.prototype.render.apply(this, arguments);

    return this;
  },

  /**
   * Updates the model to store action
   *
   * @param {Object} formData
   */
  updateModel: function (formData) {
    if (this.getParent().getCurrentOperation() === this.getCode()) {
      formData.actions = [
        {
          field: 'productModelCode',
          value: formData.product_model,
        },
      ];

      this.setData(formData, {silent: true});
    }
  },

  /**
   * {@inheritdoc}
   */
  getValue: function () {
    const action = _.findWhere(this.getFormData().actions, {field: 'productModelCode'});

    return action ? action.value : null;
  },

  /**
   * Checks there is one product model selected to go to the next step
   */
  validate: function () {
    const data = this.getFormData();
    const productModelCode = propertyAccessor.accessProperty(data, 'actions.0.value', null);

    const hasUpdate = null !== productModelCode;

    if (!hasUpdate) {
      messenger.notify('error', __('pim_enrich.mass_edit.product.operation.add_to_existing_product_model.no_update'));
    }

    return $.Deferred().resolve(hasUpdate);
  },
});
