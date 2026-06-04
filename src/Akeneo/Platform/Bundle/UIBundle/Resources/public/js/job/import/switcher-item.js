import __ from 'oro/translator';
import pimEdition from 'pim/edition';
import BaseForm from 'pim/form';

export default BaseForm.extend({
  visible: false,

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
    this.listenTo(this.getRoot(), 'switcher:switch', this.switch);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.empty();
    const {configuration} = this.getRoot().getFormData();
    const storageType = configuration.storage?.type ?? 'none';
    if (pimEdition.isCloudEdition() && this.config.hideForCloudEdition && storageType === 'none') {
      return;
    }

    this.getRoot().trigger('switcher:register', {
      label: __(this.config.label),
      code: this.code,
      allowedKey: this.config.allowedKey,
    });

    if (this.visible) {
      return BaseForm.prototype.render.apply(this, arguments);
    }

    this.delegateEvents();
  },

  /**
   * This will enable or disable the current item.
   *
   * @param {Object} event
   * @param {String} event.code The code of the current switcher item
   */
  switch: function (event) {
    this.visible = event.code === this.code;

    this.render();
  },
});
