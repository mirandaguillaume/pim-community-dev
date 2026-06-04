import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import Routing from 'routing';
import template from 'pim/template/form/index/confirm-button';

export default BaseForm.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config || {};

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        buttonClass: this.config.buttonClass,
        buttonLabel: __(this.config.buttonLabel),
        title: __(this.config.title),
        message: __(this.config.message),
        url: Routing.generate(this.config.url),
        redirectUrl: Routing.generate(this.config.redirectUrl),
        errorMessage: __(this.config.errorMessage),
        successMessage: __(this.config.successMessage),
        subTitle: __(this.config.subTitle),
      })
    );

    this.renderExtensions();

    return this;
  },
});
