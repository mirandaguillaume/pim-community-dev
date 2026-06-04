import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import 'pim/fetcher-registry';
import template from 'pim/template/family/tab/attributes';

export default BaseForm.extend({
  className: 'attributes',
  template: _.template(template),

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
    this.trigger('tab:register', {
      code: this.code,
      label: __(this.config.label),
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    this.$el.html(this.template());

    this.renderExtensions();
  },
});
