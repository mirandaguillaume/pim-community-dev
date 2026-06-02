'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
require('oro/translator');
require('routing');
var BaseOperation = __pimInterop(require('pim/mass-edit-form/product/operation'));
require('pim/user-context');
var FormBuilder = __pimInterop(require('pim/form-builder'));
var propertyAccessor = __pimInterop(require('pim/common/property'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var template = __pimInterop(require('pim/template/mass-edit/family/set-requirements'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseOperation.extend({
  template: _.template(template),
  formPromise: null,

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (null === this.getValue()) {
      this.setValue([]);
    }

    var family = {
      attributes: [],
      attribute_requirements: {},
      meta: {},
    };
    if (!this.formPromise) {
      this.formPromise = FormBuilder.build('pim-mass-family-edit-form').then(
        function (form) {
          form.setData(family);
          form.trigger('pim_enrich:form:entity:post_fetch', family);
          this.listenTo(form, 'pim_enrich:mass_edit:model_updated', this.updateModel);

          return form;
        }.bind(this)
      );
    }

    this.formPromise.then(
      function (form) {
        this.$el.html(this.template());
        form.setElement(this.$('.set-requirements')).render();
        form.trigger('pim_enrich:form:update_read_only', this.readOnly);

        // This method renders a complete PEF page, we need to remove useless elements manually.
        this.$el.find('.navigation').remove();
        this.$el.find('.AknDefault-mainContent').addClass('AknDefault-mainContent--withoutPadding');
      }.bind(this)
    );

    return this;
  },

  /**
   * Update the mass edit model each time a requirement is changed
   *
   * @param {object} data
   */
  updateModel: function (data) {
    FetcherRegistry.getFetcher('channel')
      .fetchAll()
      .then(
        function (channels) {
          var attributeRequirements = [];

          _.each(data.attributes, function (attributeCode) {
            _.each(channels, function (channel) {
              attributeRequirements.push({
                attribute_code: attributeCode,
                channel_code: channel.code,
                is_required: _.contains(
                  propertyAccessor.accessProperty(data.attribute_requirements, channel.code, []),
                  attributeCode
                ),
              });
            });
          });

          this.setValue(attributeRequirements);
        }.bind(this)
      );
  },

  /**
   * Update the general model
   *
   * @param {Object} values
   */
  setValue: function (values) {
    var data = this.getFormData();

    data.actions = values;

    analytics.appcuesTrack('grid:mass-edit:requirements-checked', {
      actions: data.actions,
    });

    this.setData(data);
  },

  /**
   * Get the value of the model
   *
   * @return {object}
   */
  getValue: function () {
    return this.getFormData().actions;
  },
});
