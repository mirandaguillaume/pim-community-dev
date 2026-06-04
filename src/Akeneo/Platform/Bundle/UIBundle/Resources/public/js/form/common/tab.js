import __ from 'oro/translator';
import BaseForm from 'pim/form';

export default BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.registerTab();

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  registerTab: function () {
    this.trigger('tab:register', {
      code: this.code,
      label: __(this.config.label),
    });
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();

    this.renderExtensions();
  },
});
