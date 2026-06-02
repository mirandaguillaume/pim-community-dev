'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseField = __pimInterop(require('pim/job/common/edit/field/field'));
var fieldTemplate = __pimInterop(require('pim/template/export/common/edit/field/switch'));
var propertyAccessor = __pimInterop(require('pim/common/property'));
require('bootstrap.bootstrapswitch');

module.exports = BaseField.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change input': 'updateState',
  },

  /**
   * {@inherit}
   */
  configure: function () {
    this.listenTo(this.getRoot(), 'job.with_label.change', () => {
      if (this.getFormData().configuration.with_label) {
        const data = propertyAccessor.updateProperty(this.getFormData(), this.getFieldCode(), true);

        this.setData(data);
      }
      this.render();
    });

    return BaseField.prototype.configure.apply(this, arguments);
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

    this.$('.switch').bootstrapSwitch();
  },

  /**
   * Get the field dom value
   *
   * @return {string}
   */
  getFieldValue: function () {
    return this.$('input[type="checkbox"]').prop('checked');
  },
});
